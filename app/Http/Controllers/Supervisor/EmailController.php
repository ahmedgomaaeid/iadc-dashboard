<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailController extends Controller
{
    private function getImapConnection($folder = 'INBOX')
    {
        $supervisor = Auth::guard('supervisor')->user();

        if (!$supervisor->server_mail || !$supervisor->server_password) {
            return null;
        }

        $host = '{iadcsuez.org:993/imap/ssl/novalidate-cert}' . $folder;

        $connection = @\imap_open($host, $supervisor->server_mail, $supervisor->server_password);

        if (!$connection) {
            return null;
        }

        return $connection;
    }

    private function getSentFolderName($connection)
    {
        $folders = \imap_list($connection, '{iadcsuez.org:993/imap/ssl/novalidate-cert}', '*');
        if ($folders) {
            foreach ($folders as $folder) {
                $folderName = str_replace('{iadcsuez.org:993/imap/ssl/novalidate-cert}', '', $folder);
                $lower = strtolower($folderName);
                if (str_contains($lower, 'sent') || str_contains($lower, 'sent mail') || str_contains($lower, 'sent items')) {
                    return $folderName;
                }
            }
        }
        return 'Sent';
    }

    public function inbox(Request $request)
    {
        $supervisor = Auth::guard('supervisor')->user();

        if (!$supervisor->server_mail || !$supervisor->server_password) {
            return view('supervisor.email.index', [
                'emails' => [],
                'currentFolder' => 'inbox',
                'page' => 1,
                'totalPages' => 1,
                'totalEmails' => 0,
                'unreadCount' => 0,
                'noCredentials' => true,
            ]);
        }

        $connection = $this->getImapConnection('INBOX');
        if (!$connection) {
            return view('supervisor.email.index', [
                'emails' => [],
                'currentFolder' => 'inbox',
                'page' => 1,
                'totalPages' => 1,
                'totalEmails' => 0,
                'unreadCount' => 0,
                'connectionError' => true,
            ]);
        }

        return $this->listEmails($connection, 'inbox', $request);
    }

    public function sent(Request $request)
    {
        $supervisor = Auth::guard('supervisor')->user();

        if (!$supervisor->server_mail || !$supervisor->server_password) {
            return view('supervisor.email.index', [
                'emails' => [],
                'currentFolder' => 'sent',
                'page' => 1,
                'totalPages' => 1,
                'totalEmails' => 0,
                'unreadCount' => 0,
                'noCredentials' => true,
            ]);
        }

        // First get the sent folder name
        $tempConnection = $this->getImapConnection('INBOX');
        if (!$tempConnection) {
            return view('supervisor.email.index', [
                'emails' => [],
                'currentFolder' => 'sent',
                'page' => 1,
                'totalPages' => 1,
                'totalEmails' => 0,
                'unreadCount' => 0,
                'connectionError' => true,
            ]);
        }
        $sentFolder = $this->getSentFolderName($tempConnection);
        \imap_close($tempConnection);

        $connection = $this->getImapConnection($sentFolder);
        if (!$connection) {
            return view('supervisor.email.index', [
                'emails' => [],
                'currentFolder' => 'sent',
                'page' => 1,
                'totalPages' => 1,
                'totalEmails' => 0,
                'unreadCount' => 0,
                'connectionError' => true,
            ]);
        }

        return $this->listEmails($connection, 'sent', $request);
    }

    private function listEmails($connection, $currentFolder, Request $request)
    {
        $perPage = 20;
        $page = max(1, (int) $request->get('page', 1));

        $mailboxInfo = \imap_check($connection);
        $totalEmails = $mailboxInfo->Nmsgs;

        // Get unread count from INBOX
        if ($currentFolder === 'inbox') {
            $unreadCount = $mailboxInfo->Nmsgs > 0 ? count(\imap_search($connection, 'UNSEEN') ?: []) : 0;
        } else {
            $unreadCount = 0;
            $inboxConn = $this->getImapConnection('INBOX');
            if ($inboxConn) {
                $unreadCount = count(\imap_search($inboxConn, 'UNSEEN') ?: []);
                \imap_close($inboxConn);
            }
        }

        $totalPages = max(1, ceil($totalEmails / $perPage));
        $page = min($page, $totalPages);

        $emails = [];
        if ($totalEmails > 0) {
            // Calculate range (newest first)
            $end = $totalEmails - (($page - 1) * $perPage);
            $start = max(1, $end - $perPage + 1);

            $sequence = "$start:$end";
            $overviews = \imap_fetch_overview($connection, $sequence, 0);

            if ($overviews) {
                // Sort by date descending
                usort($overviews, function ($a, $b) {
                    return strtotime($b->date) - strtotime($a->date);
                });

                foreach ($overviews as $overview) {
                    $emails[] = [
                        'uid' => $overview->uid,
                        'msgno' => $overview->msgno,
                        'subject' => isset($overview->subject) ? $this->decodeMimeStr($overview->subject) : '(No Subject)',
                        'from' => isset($overview->from) ? $this->decodeMimeStr($overview->from) : 'Unknown',
                        'to' => isset($overview->to) ? $this->decodeMimeStr($overview->to) : '',
                        'date' => date('M d, Y h:i A', strtotime($overview->date)),
                        'date_raw' => strtotime($overview->date),
                        'seen' => $overview->seen ?? false,
                        'flagged' => $overview->flagged ?? false,
                        'size' => $overview->size ?? 0,
                    ];
                }
            }
        }

        \imap_close($connection);

        return view('supervisor.email.index', compact(
            'emails',
            'currentFolder',
            'page',
            'totalPages',
            'totalEmails',
            'unreadCount'
        ));
    }

    public function show($folder, $uid)
    {
        $supervisor = Auth::guard('supervisor')->user();
        $imapFolder = $folder === 'sent' ? $this->findSentFolder() : 'INBOX';
        $connection = $this->getImapConnection($imapFolder);

        if (!$connection) {
            return redirect()->route('supervisor.email.inbox')
                ->with('error', 'Could not connect to mail server.');
        }

        $msgno = \imap_msgno($connection, (int) $uid);
        if (!$msgno) {
            \imap_close($connection);
            return redirect()->route('supervisor.email.inbox')
                ->with('error', 'Email not found.');
        }

        // Mark as read
        \imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID);

        // Get headers
        $header = \imap_headerinfo($connection, $msgno);
        $structure = \imap_fetchstructure($connection, $msgno);

        // Get body
        $body = $this->getBody($connection, $msgno, $structure);

        // Get attachments
        $attachments = $this->getAttachments($connection, $msgno, $structure);

        $email = [
            'uid' => $uid,
            'folder' => $folder,
            'subject' => isset($header->subject) ? $this->decodeMimeStr($header->subject) : '(No Subject)',
            'from' => $this->formatAddresses($header->from ?? []),
            'from_email' => isset($header->from[0]) ? ($header->from[0]->mailbox . '@' . ($header->from[0]->host ?? '')) : '',
            'to' => $this->formatAddresses($header->to ?? []),
            'cc' => $this->formatAddresses($header->cc ?? []),
            'date' => date('M d, Y h:i A', strtotime($header->date)),
            'body' => $body,
            'attachments' => $attachments,
        ];

        \imap_close($connection);

        return view('supervisor.email.show', compact('email'));
    }

    private function findSentFolder()
    {
        $connection = $this->getImapConnection('INBOX');
        if (!$connection) return 'Sent';
        $sentFolder = $this->getSentFolderName($connection);
        \imap_close($connection);
        return $sentFolder;
    }

    private function formatAddresses($addresses)
    {
        $formatted = [];
        foreach ($addresses as $addr) {
            $name = isset($addr->personal) ? $this->decodeMimeStr($addr->personal) : '';
            $email = ($addr->mailbox ?? '') . '@' . ($addr->host ?? '');
            $formatted[] = $name ? "$name <$email>" : $email;
        }
        return implode(', ', $formatted);
    }

    private function getBody($connection, $msgno, $structure)
    {
        // Try to get HTML body first, fallback to plain text
        $body = '';

        if (!isset($structure->parts) || !$structure->parts) {
            // Simple message
            $body = \imap_fetchbody($connection, $msgno, '1');
            $body = $this->decodeBody($body, $structure->encoding ?? 0);
            if ($structure->subtype === 'PLAIN') {
                $body = nl2br(htmlspecialchars($body));
            }
        } else {
            // Multipart message
            $htmlBody = '';
            $plainBody = '';
            $this->processStructure($connection, $msgno, $structure, '', $htmlBody, $plainBody);
            $body = $htmlBody ?: $plainBody;
        }

        // Handle charset
        if ($body) {
            $charset = $this->getCharset($structure);
            if ($charset && strtolower($charset) !== 'utf-8') {
                $converted = @iconv($charset, 'UTF-8//IGNORE', $body);
                if ($converted !== false) {
                    $body = $converted;
                }
            }
        }

        return $body;
    }

    private function processStructure($connection, $msgno, $structure, $partNumber, &$htmlBody, &$plainBody)
    {
        if (isset($structure->parts)) {
            foreach ($structure->parts as $index => $part) {
                $currentPart = $partNumber ? "$partNumber." . ($index + 1) : (string)($index + 1);

                if ($part->type === 0) { // Text
                    $body = \imap_fetchbody($connection, $msgno, $currentPart);
                    $body = $this->decodeBody($body, $part->encoding ?? 0);

                    if (strtoupper($part->subtype) === 'HTML') {
                        $htmlBody .= $body;
                    } elseif (strtoupper($part->subtype) === 'PLAIN') {
                        $plainBody .= nl2br(htmlspecialchars($body));
                    }
                } elseif ($part->type === 1) { // Multipart
                    $this->processStructure($connection, $msgno, $part, $currentPart, $htmlBody, $plainBody);
                }
            }
        }
    }

    private function decodeBody($body, $encoding)
    {
        switch ($encoding) {
            case 0: // 7BIT
            case 1: // 8BIT
                return $body;
            case 2: // BINARY
                return $body;
            case 3: // BASE64
                return base64_decode($body);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($body);
            case 5: // OTHER
                return $body;
            default:
                return $body;
        }
    }

    private function getCharset($structure)
    {
        if (isset($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) === 'charset') {
                    return $param->value;
                }
            }
        }
        if (isset($structure->ifparameters) && $structure->ifparameters && isset($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) === 'charset') {
                    return $param->value;
                }
            }
        }
        return 'UTF-8';
    }

    private function getAttachments($connection, $msgno, $structure)
    {
        $attachments = [];

        if (!isset($structure->parts) || !$structure->parts) {
            return $attachments;
        }

        foreach ($structure->parts as $index => $part) {
            $filename = '';

            // Check disposition
            if (isset($part->ifdisposition) && $part->ifdisposition) {
                if (strtolower($part->disposition) === 'attachment' || strtolower($part->disposition) === 'inline') {
                    if (isset($part->ifdparameters) && $part->ifdparameters) {
                        foreach ($part->dparameters as $dparam) {
                            if (strtolower($dparam->attribute) === 'filename') {
                                $filename = $this->decodeMimeStr($dparam->value);
                            }
                        }
                    }
                }
            }

            // Also check parameters for name
            if (!$filename && isset($part->ifparameters) && $part->ifparameters) {
                foreach ($part->parameters as $param) {
                    if (strtolower($param->attribute) === 'name') {
                        $filename = $this->decodeMimeStr($param->value);
                    }
                }
            }

            if ($filename) {
                $attachments[] = [
                    'filename' => $filename,
                    'part_number' => $index + 1,
                    'size' => $part->bytes ?? 0,
                    'encoding' => $part->encoding ?? 0,
                ];
            }
        }

        return $attachments;
    }

    public function downloadAttachment($folder, $uid, $partNumber)
    {
        $imapFolder = $folder === 'sent' ? $this->findSentFolder() : 'INBOX';
        $connection = $this->getImapConnection($imapFolder);

        if (!$connection) {
            return redirect()->back()->with('error', 'Could not connect to mail server.');
        }

        $msgno = \imap_msgno($connection, (int) $uid);
        if (!$msgno) {
            \imap_close($connection);
            return redirect()->back()->with('error', 'Email not found.');
        }

        $structure = \imap_fetchstructure($connection, $msgno);

        if (!isset($structure->parts[$partNumber - 1])) {
            \imap_close($connection);
            return redirect()->back()->with('error', 'Attachment not found.');
        }

        $part = $structure->parts[$partNumber - 1];

        // Get filename
        $filename = 'attachment';
        if (isset($part->ifdparameters) && $part->ifdparameters) {
            foreach ($part->dparameters as $dparam) {
                if (strtolower($dparam->attribute) === 'filename') {
                    $filename = $this->decodeMimeStr($dparam->value);
                }
            }
        }
        if ($filename === 'attachment' && isset($part->ifparameters) && $part->ifparameters) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    $filename = $this->decodeMimeStr($param->value);
                }
            }
        }

        $body = \imap_fetchbody($connection, $msgno, (string) $partNumber);
        $body = $this->decodeBody($body, $part->encoding ?? 0);

        \imap_close($connection);

        return response($body)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($body));
    }

    public function compose(Request $request)
    {
        $replyTo = null;

        // If replying to an email
        if ($request->has('reply_to') && $request->has('folder')) {
            $imapFolder = $request->folder === 'sent' ? $this->findSentFolder() : 'INBOX';
            $connection = $this->getImapConnection($imapFolder);

            if ($connection) {
                $msgno = \imap_msgno($connection, (int) $request->reply_to);
                if ($msgno) {
                    $header = \imap_headerinfo($connection, $msgno);
                    $structure = \imap_fetchstructure($connection, $msgno);
                    $body = $this->getBody($connection, $msgno, $structure);

                    $replyTo = [
                        'uid' => $request->reply_to,
                        'folder' => $request->folder,
                        'to' => isset($header->from[0]) ? ($header->from[0]->mailbox . '@' . ($header->from[0]->host ?? '')) : '',
                        'subject' => 'Re: ' . preg_replace('/^Re:\s*/i', '', isset($header->subject) ? $this->decodeMimeStr($header->subject) : ''),
                        'body' => $body,
                        'date' => date('M d, Y h:i A', strtotime($header->date)),
                        'from' => $this->formatAddresses($header->from ?? []),
                    ];
                }
                \imap_close($connection);
            }
        }

        return view('supervisor.email.compose', compact('replyTo'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments.*' => 'file|max:25600', // 25MB max per file
        ]);

        $supervisor = Auth::guard('supervisor')->user();

        if (!$supervisor->server_mail || !$supervisor->server_password) {
            return redirect()->back()->with('error', 'No mail credentials configured. Please update your profile.');
        }

        try {
            // Dynamically configure SMTP
            config([
                'mail.mailers.supervisor_smtp' => [
                    'transport' => 'smtp',
                    'host' => 'iadcsuez.org',
                    'port' => 465,
                    'scheme' => 'ssl',
                    'username' => $supervisor->server_mail,
                    'password' => $supervisor->server_password,
                    'timeout' => null,
                ],
            ]);

            $toEmail = $request->to;
            $subject = $request->subject;
            $bodyContent = $request->body;
            $fromEmail = $supervisor->server_mail;
            $fromName = $supervisor->name;

            Mail::mailer('supervisor_smtp')->html($bodyContent, function ($message) use ($toEmail, $subject, $fromEmail, $fromName, $request) {
                $message->from($fromEmail, $fromName)
                    ->to($toEmail)
                    ->subject($subject);

                // Handle attachments
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                            'mime' => $file->getMimeType(),
                        ]);
                    }
                }
            });

            // Try to save to Sent folder via IMAP
            $this->saveToSent($request, $supervisor);

            return redirect()->route('supervisor.email.inbox')
                ->with('success', 'Email sent successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    private function saveToSent(Request $request, $supervisor)
    {
        try {
            $connection = $this->getImapConnection('INBOX');
            if (!$connection) return;

            $sentFolder = $this->getSentFolderName($connection);
            \imap_close($connection);

            $sentConnection = $this->getImapConnection($sentFolder);
            if (!$sentConnection) return;

            $boundary = md5(time());
            $message = "From: {$supervisor->name} <{$supervisor->server_mail}>\r\n";
            $message .= "To: {$request->to}\r\n";
            $message .= "Subject: {$request->subject}\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "\r\n";
            $message .= $request->body;

            \imap_append($sentConnection, '{iadcsuez.org:993/imap/ssl/novalidate-cert}' . $sentFolder, $message, "\\Seen");
            \imap_close($sentConnection);
        } catch (\Exception $e) {
            // Silent fail - email was already sent via SMTP
        }
    }

    public function destroy($folder, $uid)
    {
        $imapFolder = $folder === 'sent' ? $this->findSentFolder() : 'INBOX';
        $connection = $this->getImapConnection($imapFolder);

        if (!$connection) {
            return redirect()->back()->with('error', 'Could not connect to mail server.');
        }

        \imap_delete($connection, (string) $uid, FT_UID);
        \imap_expunge($connection);
        \imap_close($connection);

        $redirectRoute = $folder === 'sent' ? 'supervisor.email.sent' : 'supervisor.email.inbox';
        return redirect()->route($redirectRoute)
            ->with('success', 'Email deleted successfully.');
    }

    public function toggleRead($folder, $uid)
    {
        $imapFolder = $folder === 'sent' ? $this->findSentFolder() : 'INBOX';
        $connection = $this->getImapConnection($imapFolder);

        if (!$connection) {
            return redirect()->back()->with('error', 'Could not connect to mail server.');
        }

        $msgno = \imap_msgno($connection, (int) $uid);
        if ($msgno) {
            $header = \imap_headerinfo($connection, $msgno);
            if ($header->Unseen === 'U' || $header->Recent === 'N') {
                \imap_setflag_full($connection, (string) $uid, '\\Seen', ST_UID);
            } else {
                \imap_clearflag_full($connection, (string) $uid, '\\Seen', ST_UID);
            }
        }

        \imap_close($connection);

        return redirect()->back()->with('success', 'Email status updated.');
    }

    private function decodeMimeStr($string)
    {
        $elements = \imap_mime_header_decode($string);
        $decoded = '';
        foreach ($elements as $element) {
            $charset = ($element->charset === 'default') ? 'UTF-8' : $element->charset;
            if (strtolower($charset) !== 'utf-8') {
                $converted = @iconv($charset, 'UTF-8//IGNORE', $element->text);
                $decoded .= $converted !== false ? $converted : $element->text;
            } else {
                $decoded .= $element->text;
            }
        }
        return $decoded;
    }
}
