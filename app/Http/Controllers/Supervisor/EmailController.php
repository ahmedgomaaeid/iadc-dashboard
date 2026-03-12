<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Services\ImapClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    private function getClient(): ?ImapClient
    {
        $supervisor = Auth::guard('supervisor')->user();

        if (!$supervisor->server_mail || !$supervisor->server_password) {
            return null;
        }

        $client = new ImapClient('iadcsuez.org', 993, true);

        if (!$client->connect($supervisor->server_mail, $supervisor->server_password)) {
            return null;
        }

        return $client;
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

        $client = $this->getClient();
        if (!$client) {
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

        $folderInfo = $client->selectFolder('INBOX');
        if (!$folderInfo) {
            $client->disconnect();
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

        $unreadCount = $client->getUnseenCount();
        $result = $this->listEmails($client, 'inbox', $folderInfo, $request, $unreadCount);
        $client->disconnect();
        return $result;
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

        $client = $this->getClient();
        if (!$client) {
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

        // Get inbox unread count first
        $client->selectFolder('INBOX');
        $unreadCount = $client->getUnseenCount();

        // Find and select sent folder
        $sentFolder = $client->findSentFolder();
        $folderInfo = $client->selectFolder($sentFolder);

        if (!$folderInfo) {
            $client->disconnect();
            return view('supervisor.email.index', [
                'emails' => [],
                'currentFolder' => 'sent',
                'page' => 1,
                'totalPages' => 1,
                'totalEmails' => 0,
                'unreadCount' => $unreadCount,
                'connectionError' => true,
            ]);
        }

        $result = $this->listEmails($client, 'sent', $folderInfo, $request, $unreadCount);
        $client->disconnect();
        return $result;
    }

    private function listEmails(ImapClient $client, string $currentFolder, array $folderInfo, Request $request, int $unreadCount)
    {
        $perPage = 20;
        $page = max(1, (int) $request->get('page', 1));
        $totalEmails = $folderInfo['exists'];
        $totalPages = max(1, (int) ceil($totalEmails / $perPage));
        $page = min($page, $totalPages);

        $emails = [];
        if ($totalEmails > 0) {
            // Calculate range (newest first)
            $end = $totalEmails - (($page - 1) * $perPage);
            $start = max(1, $end - $perPage + 1);

            $msgNumbers = range($start, $end);
            $headers = $client->fetchHeaders($msgNumbers);

            // Sort by message number descending (newest first)
            krsort($headers);

            foreach ($headers as $email) {
                $h = $email['headers'] ?? [];
                $seen = in_array('\\Seen', $email['flags'] ?? []);

                $emails[] = [
                    'uid' => $email['uid'] ?? $email['msgno'],
                    'msgno' => $email['msgno'],
                    'subject' => $h['subject'] ?? '(No Subject)',
                    'from' => $h['from'] ?? 'Unknown',
                    'to' => $h['to'] ?? '',
                    'date' => isset($h['date']) ? date('M d, Y h:i A', strtotime($h['date'])) : '',
                    'date_raw' => isset($h['date']) ? strtotime($h['date']) : 0,
                    'seen' => $seen,
                    'flagged' => in_array('\\Flagged', $email['flags'] ?? []),
                    'size' => 0,
                ];
            }
        }

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
        $client = $this->getClient();
        if (!$client) {
            return redirect()->route('supervisor.email.inbox')
                ->with('error', 'Could not connect to mail server.');
        }

        // Select the right folder
        if ($folder === 'sent') {
            $sentFolder = $client->findSentFolder();
            $client->selectFolder($sentFolder);
        } else {
            $client->selectFolder('INBOX');
        }

        // UID to message number: search for the UID
        $msgno = $this->uidToMsgno($client, (int) $uid);
        if (!$msgno) {
            $client->disconnect();
            return redirect()->route('supervisor.email.inbox')
                ->with('error', 'Email not found.');
        }

        // Mark as read
        $client->markAsSeen($msgno);

        // Fetch full message
        $message = $client->fetchMessage($msgno);

        if (!$message) {
            $client->disconnect();
            return redirect()->route('supervisor.email.inbox')
                ->with('error', 'Could not load email.');
        }

        $h = $message['headers'] ?? [];

        $email = [
            'uid' => $uid,
            'folder' => $folder,
            'subject' => $h['subject'] ?? '(No Subject)',
            'from' => $h['from'] ?? 'Unknown',
            'from_email' => $this->extractEmail($h['from'] ?? ''),
            'to' => $h['to'] ?? '',
            'cc' => $h['cc'] ?? '',
            'date' => isset($h['date']) ? date('M d, Y h:i A', strtotime($h['date'])) : '',
            'body' => $message['body'] ?? '',
            'attachments' => $message['attachments'] ?? [],
        ];

        $client->disconnect();

        return view('supervisor.email.show', compact('email'));
    }

    private function uidToMsgno(ImapClient $client, int $uid): ?int
    {
        // Search for the message with this UID
        $response = $client->search("UID $uid");
        return !empty($response) ? $response[0] : null;
    }

    private function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return $m[1];
        }
        if (filter_var(trim($from), FILTER_VALIDATE_EMAIL)) {
            return trim($from);
        }
        return $from;
    }

    public function downloadAttachment($folder, $uid, $partNumber)
    {
        $client = $this->getClient();
        if (!$client) {
            return redirect()->back()->with('error', 'Could not connect to mail server.');
        }

        if ($folder === 'sent') {
            $client->selectFolder($client->findSentFolder());
        } else {
            $client->selectFolder('INBOX');
        }

        $msgno = $this->uidToMsgno($client, (int) $uid);
        if (!$msgno) {
            $client->disconnect();
            return redirect()->back()->with('error', 'Email not found.');
        }

        $attachment = $client->fetchAttachment($msgno, (int) $partNumber);
        $client->disconnect();

        if (!$attachment) {
            return redirect()->back()->with('error', 'Attachment not found.');
        }

        return response($attachment['content'])
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $attachment['filename'] . '"')
            ->header('Content-Length', strlen($attachment['content']));
    }

    public function compose(Request $request)
    {
        $replyTo = null;

        if ($request->has('reply_to') && $request->has('folder')) {
            $client = $this->getClient();
            if ($client) {
                if ($request->folder === 'sent') {
                    $client->selectFolder($client->findSentFolder());
                } else {
                    $client->selectFolder('INBOX');
                }

                $msgno = $this->uidToMsgno($client, (int) $request->reply_to);
                if ($msgno) {
                    $message = $client->fetchMessage($msgno);
                    $h = $message['headers'] ?? [];

                    $replyTo = [
                        'uid' => $request->reply_to,
                        'folder' => $request->folder,
                        'to' => $this->extractEmail($h['from'] ?? ''),
                        'subject' => 'Re: ' . preg_replace('/^Re:\s*/i', '', $h['subject'] ?? ''),
                        'body' => $message['body'] ?? '',
                        'date' => isset($h['date']) ? date('M d, Y h:i A', strtotime($h['date'])) : '',
                        'from' => $h['from'] ?? '',
                    ];
                }
                $client->disconnect();
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
            'attachments.*' => 'file|max:25600',
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
                    'host' => env('MAIL_HOST', '72.61.98.190'),
                    'port' => env('MAIL_PORT', 465),
                    'scheme' => env('MAIL_SCHEME', 'smtps'),
                    'username' => $supervisor->server_mail,
                    'password' => $supervisor->server_password,
                    'timeout' => null,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
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

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                            'mime' => $file->getMimeType(),
                        ]);
                    }
                }
            });

            // Try to save to Sent folder
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
            $client = $this->getClient();
            if (!$client) return;

            $sentFolder = $client->findSentFolder();

            $message = "From: {$supervisor->name} <{$supervisor->server_mail}>\r\n";
            $message .= "To: {$request->to}\r\n";
            $message .= "Subject: {$request->subject}\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "\r\n";
            $message .= $request->body;

            $client->selectFolder($sentFolder);
            $client->appendMessage($sentFolder, $message);
            $client->disconnect();
        } catch (\Exception $e) {
            // Silent fail - email was already sent via SMTP
        }
    }

    public function destroy($folder, $uid)
    {
        $client = $this->getClient();
        if (!$client) {
            return redirect()->back()->with('error', 'Could not connect to mail server.');
        }

        if ($folder === 'sent') {
            $client->selectFolder($client->findSentFolder());
        } else {
            $client->selectFolder('INBOX');
        }

        $msgno = $this->uidToMsgno($client, (int) $uid);
        if ($msgno) {
            $client->deleteMessage($msgno);
        }

        $client->disconnect();

        $redirectRoute = $folder === 'sent' ? 'supervisor.email.sent' : 'supervisor.email.inbox';
        return redirect()->route($redirectRoute)
            ->with('success', 'Email deleted successfully.');
    }

    public function toggleRead($folder, $uid)
    {
        $client = $this->getClient();
        if (!$client) {
            return redirect()->back()->with('error', 'Could not connect to mail server.');
        }

        if ($folder === 'sent') {
            $client->selectFolder($client->findSentFolder());
        } else {
            $client->selectFolder('INBOX');
        }

        $msgno = $this->uidToMsgno($client, (int) $uid);
        if ($msgno) {
            $message = $client->fetchMessage($msgno);
            $seen = in_array('\\Seen', $message['flags'] ?? []);

            if ($seen) {
                $client->markAsUnseen($msgno);
            } else {
                $client->markAsSeen($msgno);
            }
        }

        $client->disconnect();
        return redirect()->back()->with('success', 'Email status updated.');
    }
}
