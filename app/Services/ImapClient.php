<?php

namespace App\Services;

/**
 * Socket-based IMAP client that doesn't require the PHP IMAP extension.
 * Uses PHP's stream_socket_client for raw IMAP protocol communication.
 */
class ImapClient
{
    private $socket;
    private int $tagCounter = 0;
    private string $host;
    private int $port;
    private bool $ssl;

    public function __construct(string $host, int $port = 993, bool $ssl = true)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;
    }

    /**
     * Connect and login to the IMAP server
     */
    public function connect(string $username, string $password): bool
    {
        $prefix = $this->ssl ? 'ssl://' : 'tcp://';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $this->socket = @stream_socket_client(
            $prefix . $this->host . ':' . $this->port,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->socket) {
            return false;
        }

        // Read greeting
        $this->readLine();

        // Login
        $response = $this->command("LOGIN \"$username\" \"$password\"");
        return $this->isOk($response);
    }

    /**
     * Select a mailbox folder
     */
    public function selectFolder(string $folder = 'INBOX'): ?array
    {
        $response = $this->command("SELECT \"$folder\"");
        if (!$this->isOk($response)) {
            return null;
        }

        $info = ['exists' => 0, 'recent' => 0, 'unseen' => 0];
        foreach ($response as $line) {
            if (preg_match('/\*\s+(\d+)\s+EXISTS/i', $line, $m)) {
                $info['exists'] = (int)$m[1];
            }
            if (preg_match('/\*\s+(\d+)\s+RECENT/i', $line, $m)) {
                $info['recent'] = (int)$m[1];
            }
            if (preg_match('/UNSEEN\s+(\d+)/i', $line, $m)) {
                $info['unseen'] = (int)$m[1];
            }
        }
        return $info;
    }

    /**
     * List available folders
     */
    public function listFolders(): array
    {
        $response = $this->command('LIST "" "*"');
        $folders = [];
        foreach ($response as $line) {
            if (preg_match('/\*\s+LIST\s+\([^)]*\)\s+"[^"]*"\s+"?([^"\r\n]+)"?/i', $line, $m)) {
                $folders[] = trim($m[1], '"');
            }
        }
        return $folders;
    }

    /**
     * Search for emails
     */
    public function search(string $criteria = 'ALL'): array
    {
        $response = $this->command("SEARCH $criteria");
        $ids = [];
        foreach ($response as $line) {
            if (preg_match('/^\*\s+SEARCH\s+([\d\s]+)/i', $line, $m)) {
                $ids = array_map('intval', array_filter(explode(' ', trim($m[1]))));
            }
        }
        return $ids;
    }

    /**
     * Fetch email headers for a range of message numbers
     */
    public function fetchHeaders(array $msgNumbers): array
    {
        if (empty($msgNumbers)) return [];

        $emails = [];
        $sequence = implode(',', $msgNumbers);

        $response = $this->command("FETCH $sequence (UID FLAGS BODY.PEEK[HEADER.FIELDS (FROM TO SUBJECT DATE CC)])");

        $current = null;
        $headerData = '';
        $inHeader = false;

        foreach ($response as $line) {
            // Start of a new FETCH response
            if (preg_match('/^\*\s+(\d+)\s+FETCH\s*\(/i', $line, $m)) {
                // Save previous email
                if ($current !== null && $headerData) {
                    $emails[$current['msgno']]['headers'] = $this->parseHeaders($headerData);
                    $headerData = '';
                }

                $msgno = (int)$m[1];
                $current = ['msgno' => $msgno, 'uid' => 0, 'flags' => []];

                // Extract UID
                if (preg_match('/UID\s+(\d+)/i', $line, $um)) {
                    $current['uid'] = (int)$um[1];
                }

                // Extract FLAGS
                if (preg_match('/FLAGS\s*\(([^)]*)\)/i', $line, $fm)) {
                    $current['flags'] = array_filter(explode(' ', $fm[1]));
                }

                $emails[$msgno] = $current;
                $inHeader = true;
                continue;
            }

            // Closing paren of FETCH
            if ($inHeader && trim($line) === ')') {
                if ($current !== null && $headerData) {
                    $emails[$current['msgno']]['headers'] = $this->parseHeaders($headerData);
                    $headerData = '';
                }
                $inHeader = false;
                continue;
            }

            // Accumulate header lines
            if ($inHeader && $current !== null) {
                $headerData .= $line . "\r\n";
            }
        }

        // Handle last email
        if ($current !== null && $headerData) {
            $emails[$current['msgno']]['headers'] = $this->parseHeaders($headerData);
        }

        return $emails;
    }

    /**
     * Fetch a full email by message number
     */
    public function fetchMessage(int $msgno): ?array
    {
        // Fetch structure info, headers, and body
        $response = $this->command("FETCH $msgno (UID FLAGS BODY.PEEK[HEADER] BODYSTRUCTURE)");

        $email = ['msgno' => $msgno, 'uid' => 0, 'flags' => [], 'headers' => [], 'bodystructure' => ''];
        $headerData = '';
        $inHeader = false;

        foreach ($response as $line) {
            if (preg_match('/^\*\s+\d+\s+FETCH\s*\(/i', $line)) {
                if (preg_match('/UID\s+(\d+)/i', $line, $um)) {
                    $email['uid'] = (int)$um[1];
                }
                if (preg_match('/FLAGS\s*\(([^)]*)\)/i', $line, $fm)) {
                    $email['flags'] = array_filter(explode(' ', $fm[1]));
                }
                $inHeader = true;
                continue;
            }

            if ($inHeader && (trim($line) === ')' || preg_match('/^\w+\s+OK/i', $line))) {
                $inHeader = false;
                continue;
            }

            if ($inHeader) {
                $headerData .= $line . "\r\n";
            }
        }

        $email['headers'] = $this->parseHeaders($headerData);

        // Fetch full body (HTML preferred)
        $email['body'] = $this->fetchBody($msgno);

        // Fetch attachments info
        $email['attachments'] = $this->fetchAttachmentsList($msgno);

        return $email;
    }

    /**
     * Fetch email body - tries HTML first, then plain text
     */
    public function fetchBody(int $msgno): string
    {
        // Try fetching the full RFC822 body
        $response = $this->command("FETCH $msgno BODY.PEEK[]");

        $body = '';
        $inBody = false;
        $bytesExpected = 0;
        $bytesRead = 0;

        foreach ($response as $line) {
            if (!$inBody && preg_match('/\{(\d+)\}$/i', $line, $m)) {
                $bytesExpected = (int)$m[1];
                $inBody = true;
                continue;
            }

            if ($inBody) {
                if (preg_match('/^\w+\s+OK/i', $line) || trim($line) === ')') {
                    continue;
                }
                $body .= $line . "\r\n";
            }
        }

        // Parse the raw email to extract body content
        return $this->extractBodyFromRaw($body);
    }

    /**
     * Extract body content from raw email
     */
    private function extractBodyFromRaw(string $raw): string
    {
        // Split headers and body
        $parts = preg_split('/\r?\n\r?\n/', $raw, 2);
        if (count($parts) < 2) return nl2br(htmlspecialchars($raw));

        $headers = $parts[0];
        $body = $parts[1];

        // Check content type
        $contentType = '';
        $boundary = '';
        $encoding = '';
        $charset = 'UTF-8';

        if (preg_match('/Content-Type:\s*([^\r\n;]+)/i', $headers, $m)) {
            $contentType = strtolower(trim($m[1]));
        }
        if (preg_match('/boundary="?([^"\r\n;]+)"?/i', $headers, $m)) {
            $boundary = trim($m[1]);
        }
        if (preg_match('/Content-Transfer-Encoding:\s*(\S+)/i', $headers, $m)) {
            $encoding = strtolower(trim($m[1]));
        }
        if (preg_match('/charset="?([^"\r\n;]+)"?/i', $headers, $m)) {
            $charset = strtoupper(trim($m[1]));
        }

        // Multipart message
        if ($boundary) {
            return $this->parseMultipart($body, $boundary);
        }

        // Simple message
        $body = $this->decodeContent($body, $encoding);
        if ($charset !== 'UTF-8') {
            $converted = @iconv($charset, 'UTF-8//IGNORE', $body);
            if ($converted !== false) $body = $converted;
        }

        if (str_contains($contentType, 'html')) {
            return $body;
        }

        return nl2br(htmlspecialchars($body));
    }

    /**
     * Parse multipart MIME message
     */
    private function parseMultipart(string $body, string $boundary): string
    {
        $parts = explode('--' . $boundary, $body);
        $htmlBody = '';
        $plainBody = '';

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || $part === '--') continue;

            // Split part headers and content
            $sections = preg_split('/\r?\n\r?\n/', $part, 2);
            if (count($sections) < 2) continue;

            $partHeaders = $sections[0];
            $partContent = $sections[1];

            // Check for nested multipart
            if (preg_match('/boundary="?([^"\r\n;]+)"?/i', $partHeaders, $m)) {
                $nestedResult = $this->parseMultipart($partContent, trim($m[1]));
                if ($nestedResult) {
                    if (str_contains($nestedResult, '<') && str_contains($nestedResult, '>')) {
                        $htmlBody .= $nestedResult;
                    } else {
                        $plainBody .= $nestedResult;
                    }
                }
                continue;
            }

            // Skip attachments
            if (preg_match('/Content-Disposition:\s*attachment/i', $partHeaders)) {
                continue;
            }

            $partType = '';
            $partEncoding = '';
            $partCharset = 'UTF-8';

            if (preg_match('/Content-Type:\s*([^\r\n;]+)/i', $partHeaders, $m)) {
                $partType = strtolower(trim($m[1]));
            }
            if (preg_match('/Content-Transfer-Encoding:\s*(\S+)/i', $partHeaders, $m)) {
                $partEncoding = strtolower(trim($m[1]));
            }
            if (preg_match('/charset="?([^"\r\n;]+)"?/i', $partHeaders, $m)) {
                $partCharset = strtoupper(trim($m[1]));
            }

            $partContent = $this->decodeContent($partContent, $partEncoding);
            if ($partCharset !== 'UTF-8') {
                $converted = @iconv($partCharset, 'UTF-8//IGNORE', $partContent);
                if ($converted !== false) $partContent = $converted;
            }

            if (str_contains($partType, 'html')) {
                $htmlBody .= $partContent;
            } elseif (str_contains($partType, 'plain')) {
                $plainBody .= nl2br(htmlspecialchars($partContent));
            }
        }

        return $htmlBody ?: $plainBody;
    }

    /**
     * Decode content based on encoding
     */
    private function decodeContent(string $content, string $encoding): string
    {
        return match($encoding) {
            'base64' => base64_decode($content),
            'quoted-printable' => quoted_printable_decode($content),
            default => $content,
        };
    }

    /**
     * Fetch attachment list for a message
     */
    public function fetchAttachmentsList(int $msgno): array
    {
        $response = $this->command("FETCH $msgno BODY.PEEK[HEADER]");

        $raw = implode("\r\n", $response);
        $attachments = [];

        // Fetch full message to scan for attachments
        $fullResponse = $this->command("FETCH $msgno BODY.PEEK[]");
        $fullRaw = implode("\r\n", $fullResponse);

        preg_match_all('/Content-Disposition:\s*attachment[\s\S]*?filename="?([^"\r\n;]+)"?/i', $fullRaw, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $filename) {
                $attachments[] = [
                    'filename' => $this->decodeMimeString(trim($filename)),
                    'part_number' => $index + 1,
                    'size' => 0,
                ];
            }
        }

        return $attachments;
    }

    /**
     * Download an attachment by part index
     */
    public function fetchAttachment(int $msgno, int $partIndex): ?array
    {
        $fullResponse = $this->command("FETCH $msgno BODY.PEEK[]");
        $fullRaw = implode("\r\n", $fullResponse);

        // Split by boundary
        if (!preg_match('/boundary="?([^"\r\n;]+)"?/i', $fullRaw, $bm)) {
            return null;
        }

        $boundary = $bm[1];
        $parts = explode('--' . $boundary, $fullRaw);
        $attachmentIndex = 0;

        foreach ($parts as $part) {
            if (!preg_match('/Content-Disposition:\s*attachment/i', $part)) {
                continue;
            }

            $attachmentIndex++;
            if ($attachmentIndex !== $partIndex) {
                continue;
            }

            $filename = 'attachment';
            if (preg_match('/filename="?([^"\r\n;]+)"?/i', $part, $fm)) {
                $filename = $this->decodeMimeString(trim($fm[1]));
            }

            $encoding = '';
            if (preg_match('/Content-Transfer-Encoding:\s*(\S+)/i', $part, $em)) {
                $encoding = strtolower(trim($em[1]));
            }

            // Extract content after headers
            $sections = preg_split('/\r?\n\r?\n/', $part, 2);
            $content = $sections[1] ?? '';
            $content = $this->decodeContent(trim($content), $encoding);

            return [
                'filename' => $filename,
                'content' => $content,
            ];
        }

        return null;
    }

    /**
     * Mark message as seen
     */
    public function markAsSeen(int $msgno): bool
    {
        $response = $this->command("STORE $msgno +FLAGS (\\Seen)");
        return $this->isOk($response);
    }

    /**
     * Mark message as unseen
     */
    public function markAsUnseen(int $msgno): bool
    {
        $response = $this->command("STORE $msgno -FLAGS (\\Seen)");
        return $this->isOk($response);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(int $msgno): bool
    {
        $this->command("STORE $msgno +FLAGS (\\Deleted)");
        $response = $this->command("EXPUNGE");
        return $this->isOk($response);
    }

    /**
     * Append a message to a folder
     */
    public function appendMessage(string $folder, string $message, string $flags = '\\Seen'): bool
    {
        $size = strlen($message);
        $tag = $this->nextTag();
        $cmd = "$tag APPEND \"$folder\" ($flags) {" . $size . "}\r\n";

        fwrite($this->socket, $cmd);

        // Wait for continuation response (+)
        $line = $this->readLine();
        if (strpos($line, '+') !== 0) {
            return false;
        }

        // Send the message
        fwrite($this->socket, $message . "\r\n");

        // Read response
        $response = [];
        while (true) {
            $line = $this->readLine();
            $response[] = $line;
            if (preg_match("/^$tag\s+(OK|NO|BAD)/i", $line)) {
                break;
            }
        }

        return $this->isOk($response);
    }

    /**
     * Get the sent folder name
     */
    public function findSentFolder(): string
    {
        $folders = $this->listFolders();
        foreach ($folders as $folder) {
            $lower = strtolower($folder);
            if (str_contains($lower, 'sent')) {
                return $folder;
            }
        }
        return 'Sent';
    }

    /**
     * Get unseen count for current folder
     */
    public function getUnseenCount(): int
    {
        $ids = $this->search('UNSEEN');
        return count($ids);
    }

    /**
     * Parse raw headers into associative array
     */
    private function parseHeaders(string $raw): array
    {
        $headers = [];
        $lines = preg_split('/\r?\n/', $raw);
        $currentKey = '';
        $currentValue = '';

        foreach ($lines as $line) {
            if ($line === '' || $line === ')') continue;

            // Continuation line (starts with whitespace)
            if (preg_match('/^\s+/', $line) && $currentKey) {
                $currentValue .= ' ' . trim($line);
                continue;
            }

            // Save previous header
            if ($currentKey) {
                $headers[strtolower($currentKey)] = $this->decodeMimeString(trim($currentValue));
            }

            // New header
            if (preg_match('/^([A-Za-z-]+):\s*(.*)$/', $line, $m)) {
                $currentKey = $m[1];
                $currentValue = $m[2];
            } else {
                $currentKey = '';
            }
        }

        // Save last header
        if ($currentKey) {
            $headers[strtolower($currentKey)] = $this->decodeMimeString(trim($currentValue));
        }

        return $headers;
    }

    /**
     * Decode MIME encoded string (=?charset?encoding?text?=)
     */
    public function decodeMimeString(string $string): string
    {
        if (!str_contains($string, '=?')) {
            return $string;
        }

        $decoded = preg_replace_callback(
            '/=\?([^?]+)\?([BQ])\?([^?]+)\?=/i',
            function ($matches) {
                $charset = $matches[1];
                $encoding = strtoupper($matches[2]);
                $text = $matches[3];

                if ($encoding === 'B') {
                    $text = base64_decode($text);
                } elseif ($encoding === 'Q') {
                    $text = quoted_printable_decode(str_replace('_', ' ', $text));
                }

                if (strtoupper($charset) !== 'UTF-8') {
                    $converted = @iconv($charset, 'UTF-8//IGNORE', $text);
                    if ($converted !== false) return $converted;
                }

                return $text;
            },
            $string
        );

        return $decoded ?? $string;
    }

    /**
     * Send an IMAP command and return response lines
     */
    private function command(string $cmd): array
    {
        $tag = $this->nextTag();
        $fullCmd = "$tag $cmd\r\n";

        fwrite($this->socket, $fullCmd);

        $response = [];
        while (true) {
            $line = $this->readLine();
            if ($line === false) break;

            foreach (preg_split('/\r?\n/', $line) as $subLine) {
                $response[] = $subLine;
            }

            if (preg_match("/^$tag\s+(OK|NO|BAD)/i", $line)) {
                break;
            }
        }

        return $response;
    }

    /**
     * Read a line from the socket
     */
    private function readLine(): string|false
    {
        if (!$this->socket || feof($this->socket)) {
            return false;
        }

        $line = '';
        while (true) {
            $char = fgets($this->socket, 8192);
            if ($char === false) return false;

            $line .= $char;

            // Check for literal {N} continuation
            if (preg_match('/\{(\d+)\}\r?\n$/', $line, $m)) {
                $bytes = (int)$m[1];
                $data = '';
                $remaining = $bytes;
                while ($remaining > 0) {
                    $chunk = fread($this->socket, min($remaining, 8192));
                    if ($chunk === false) break;
                    $data .= $chunk;
                    $remaining -= strlen($chunk);
                }
                $line .= $data;
                continue;
            }

            break;
        }

        return rtrim($line, "\r\n");
    }

    /**
     * Generate next command tag
     */
    private function nextTag(): string
    {
        $this->tagCounter++;
        return 'A' . str_pad($this->tagCounter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if response indicates success
     */
    private function isOk(array $response): bool
    {
        if (empty($response)) return false;
        $lastLine = end($response);
        return (bool)preg_match('/OK/i', $lastLine);
    }

    /**
     * Close the connection
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            $this->command('LOGOUT');
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
