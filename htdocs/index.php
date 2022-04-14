<?php

#phpinfo();
#exit;

error_reporting(E_ALL ^ (E_DEPRECATED));

require __DIR__ . '/vendor/autoload.php';
use EmailEnginePhp\EmailEngine;

$ee = new EmailEngine(array(
    "access_token" => $_SERVER['EE_API_TOKEN'],
    "ee_base_url" => $_SERVER['EE_BASE_URL'],
));

$base_url = $_SERVER['PHP_SELF'];

$renderer = new \Handlebars\Handlebars(array(
    'loader' => new \Handlebars\Loader\StringLoader(),
    'helpers' => new \Handlebars\Helpers(),
    'enableDataVariables' => true,
));

$layout = '<!doctype html>
<html>
    <head>
        <title>EmailEngine demo</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    </head>

    <body class="py-4">
        <div class="container">
            <h1 class="mb-3">Demo webmail</h1>
            <hr class="mb-3">
            {{{body}}}
        </div>
    </body>
</html>
';

$templates = array(
    'list' => '

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active" aria-current="page">Accounts</li>
  </ol>
</nav>

<h3>Select email account</h3>

<div class="list-group">
{{#each accounts}}
    <a href="{{base_url}}?action=account&amp;account={{account}}" class="list-group-item list-group-item-action">{{name}} &lt;{{email}}&gt;</a>
{{/each}}
</div>

{{#if paging}}
    <nav>
        <ul class="pagination">
        {{#each paging}}
            <li class="page-item {{#if selected}}active{{/if}}"><a class="page-link" href="{{{url}}}">{{text}}</a></li>
        {{/each}}
        </ul>
    </nav>
{{/if}}

',

    'account' => '

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{base_url}}">Accounts</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{account.name}}</li>
  </ol>
</nav>

<div class="row mt-3">
    <div class="col-4">

        <div class="list-group">
        {{#each mailboxes}}
            <a href="{{base_url}}?action=account&amp;account={{../account.account}}&amp;mailbox={{path}}"
                class="list-group-item list-group-item-action {{#if selected}}active{{/if}}">{{name}}</a>
        {{/each}}
        </div>

    </div>
    <div class="col-8">

    <table class="table table-striped table-bordered table-hover table-sm">
        <thead>
            <tr>
                <th>From</th>
                <th>Subject</th>
            </tr>
        </thead>
        <tbody>
        {{#each messages.messages}}
            <tr>
                <td class="w-25">
                    {{#if from.name}}
                        <a href="mailto:{{from.address}}">{{from.name}}</a>
                    {{else}}
                        <a href="mailto:{{from.address}}">{{from.address}}</a>
                    {{/if}}
                </td>
                <td>

                    {{#if attachments}}
                        <div class="float-right">📎</div>
                    {{/if}}

                    <a href="{{base_url}}?action=message&amp;account={{../account.account}}&amp;message={{id}}">{{subject}}</a>
                </td>
            </tr>
        {{/each}}
        </tbody>
    </table>


    {{#if messages.paging}}
    <nav>
        <ul class="pagination">
        {{#each messages.paging}}
            <li class="page-item {{#if selected}}active{{/if}}"><a class="page-link" href="{{{url}}}">{{text}}</a></li>
        {{/each}}
        </ul>
    </nav>
    {{/if}}

    </div>
  </div>
',

    'message' => '

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{base_url}}">Accounts</a></li>
    <li class="breadcrumb-item"><a href="{{base_url}}?action=account&account={{account.account}}">{{account.name}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">View message</li>
  </ol>
</nav>

<h3>{{message.subject}}</h3>

{{#if message.from}}
<div>
    <strong>From:</strong>
    <span >
        {{#if message.from.name}}
            <span>{{message.from.name}}</span>
        {{/if}}
        <a href="mailto:{{message.from.address}}" rel="tooltip" title="{{message.from.address}}">&lt;{{message.from.address}}&gt;</a>
    </span>
</div>
{{/if}}

{{#if message.to}}
<div>
    <strong>To:</strong>
    {{#each message.to}}
    <span class="address-list-elm">
        {{#if name}}
            <span>{{name}}</span>
        {{/if}}
        <a href="mailto:{{address}}" rel="tooltip" title="{{address}}">&lt;{{address}}&gt;</a>
    </span>
    {{/each}}
</div>
{{/if}}

{{#if message.cc}}
<div>
    <strong>Cc:</strong>
    {{#each message.cc}}
    <span class="address-list-elm">
        {{#if name}}
            <span>{{name}}</span>
        {{/if}}
        <a href="mailto:{{address}}" rel="tooltip" title="{{address}}">&lt;{{address}}&gt;</a>
    </span>
    {{/each}}
</div>
{{/if}}

<div class="mt-3">
    {{#if message.text.html}}
        {{{message.text.html}}}
    {{else}}
        <pre>{{message.text.plain}}</pre>
    {{/if}}
</div>

{{#if message.attachments}}
<div class="mt-3 alert alert-secondary">

    {{#each message.attachments}}
        <a href="{{base_url}}?action=attachment&amp;account={{../account.account}}&amp;attachment={{id}}" class="btn btn-sm btn-primary">
            {{#if filename}}
                {{filename}}
            {{else}}
                {{contentType}}
            {{/if}}
            📎
        </a>
    {{/each}}

</div>
{{/if}}

',

    'error' => '

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{base_url}}">Accounts</a></li>
  </ol>
</nav>

<h3>Error {{status_code}}</h3>
<p>{{error_message}}</p>
',
);

function render_page($status_code, $body_template, $data)
{
    global $renderer, $layout, $templates, $base_url;

    http_response_code($status_code);
    header('Content-type: text/html; Charset=utf-8');

    $data['base_url'] = $base_url;
    $data['body'] = $renderer->render($templates[$body_template], $data);
    $data['body_template'] = $body_template;

    echo $renderer->render($layout, $data);
}

function show_list($page_nr)
{
    global $ee, $base_url;

    try {
        $accounts_response = $ee->request('get', '/v1/accounts?page=' . $page_nr);
    } catch (\GuzzleHttp\Exception\RequestException$e) {
        return show_error($e->getResponse()->getStatusCode(), $e->getMessage());
    } catch (Exception $e) {
        return show_error(500, 'Request failed');
    }

    $accounts_response['paging'] = array();
    if ($accounts_response['pages'] > 1) {
        for ($i = 0; $i < $accounts_response['pages']; $i++) {
            $accounts_response['paging'][] = array(
                'url' => $base_url . '?action=list&amp;page=' . $i,
                'text' => $i + 1,
                'selected' => $i === $page_nr,
            );
        }
    }

    render_page(200, 'list', $accounts_response);
}

function show_account($account_id, $requested_path, $page_nr)
{
    global $ee, $base_url;

    try {
        $account = $ee->request('get', '/v1/account/' . urlencode($account_id));
        $mailboxes = $ee->request('get', '/v1/account/' . urlencode($account_id) . '/mailboxes');
    } catch (\GuzzleHttp\Exception\RequestException$e) {
        return show_error($e->getResponse()->getStatusCode(), $e->getMessage());
    } catch (Exception $e) {
        return show_error(500, 'Request failed');
    }

    $selected_path = false;
    foreach ($mailboxes['mailboxes'] as &$mailbox) {
        if ($mailbox['path'] === $requested_path) {
            $mailbox['selected'] = true;
            $selected_path = $requested_path;
        }
    }

    $messages = array('total' => 0, 'page' => 0, 'pages' => 0, 'messages' => array());
    if ($selected_path) {
        try {
            $messages = $ee->request('get', '/v1/account/' . urlencode($account_id) . '/messages?path=' . urlencode($selected_path) . '&page=' . $page_nr);
        } catch (\GuzzleHttp\Exception\RequestException$e) {
            return show_error($e->getResponse()->getStatusCode(), $e->getMessage());
        } catch (Exception $e) {
            return show_error(500, 'Request failed');
        }

        $messages['paging'] = array();
        if ($messages['pages'] > 1) {
            for ($i = 0; $i < $messages['pages']; $i++) {
                $messages['paging'][] = array(
                    'url' => $base_url . '?action=account&amp;account=' . urlencode($account_id) . '&amp;mailbox=' . urlencode($requested_path) . '&amp;page=' . $i,
                    'text' => $i + 1,
                    'selected' => $i === $page_nr,
                );
            }
        }
    }

    render_page(200, 'account', array('account' => $account, 'mailboxes' => $mailboxes['mailboxes'], 'messages' => $messages));
}

function show_message($account_id, $message_id)
{

    global $ee, $base_url;

    try {
        $account = $ee->request('get', '/v1/account/' . urlencode($account_id));
        $message = $ee->request('get', '/v1/account/' . urlencode($account_id) . '/message/' . urlencode($message_id) . '?textType=' . urlencode('*'));
    } catch (\GuzzleHttp\Exception\RequestException$e) {
        return show_error($e->getResponse()->getStatusCode(), $e->getMessage());
    } catch (Exception $e) {
        return show_error(500, 'Request failed');
    }

    // replace embedded image links
    if (isset($message['text']) && isset($message['text']['html']) && isset($message['attachments'])) {
        foreach ($message['attachments'] as $attachment) {
            if (isset($attachment['contentId'])) {
                $cid = trim($attachment['contentId'], '<> \n\r\t\v\x00');
                $link = $base_url . '?action=attachment&amp;account=' . urlencode($account_id) . '&amp;attachment=' . urlencode($attachment['id']);
                $message['text']['html'] = str_replace('src="cid:' . $cid . '"', "src=\"$link\"", $message['text']['html']);
            }
        }
    }

    render_page(200, 'message', array('account' => $account, 'message' => $message));
}

function send_attachment($account_id, $attachment_id)
{
    global $ee;

    try {
        $account = $ee->request('get', '/v1/account/' . urlencode($account_id));
        $ee->download('/v1/account/' . urlencode($account_id) . '/attachment/' . urlencode($attachment_id));
    } catch (\GuzzleHttp\Exception\RequestException$e) {
        return show_error($e->getResponse()->getStatusCode(), $e->getMessage());
    } catch (Exception $e) {
        return show_error(500, 'Request failed');
    }
}

function show_error($status_code, $error_message)
{
    render_page($status_code, 'error', array('status_code' => $status_code, 'error_message' => $error_message));
}

if (isset($_GET['action'])) {
    $page_action = $_GET['action'];
} else {
    $page_action = 'list';
}

switch ($page_action) {
    case 'list':
        if (isset($_GET['page'])) {
            $page_nr = intval($_GET['page']);
        } else {
            $page_nr = 0;
        }
        return show_list(intval($page_nr));

    case 'account':
        if (!isset($_GET['account'])) {
            return show_error(404, 'Account ID missing');
        }

        if (isset($_GET['mailbox'])) {
            $mailbox = $_GET['mailbox'];
        } else {
            $mailbox = 'INBOX';
        }

        if (isset($_GET['page'])) {
            $page_nr = intval($_GET['page']);
        } else {
            $page_nr = 0;
        }

        return show_account($_GET['account'], $mailbox, $page_nr);

    case 'message':
        if (!isset($_GET['account'])) {
            return show_error(404, 'Account ID missing');
        }

        if (!isset($_GET['message'])) {
            return show_error(404, 'Message ID missing');
        }

        return show_message($_GET['account'], $_GET['message']);

    case 'attachment':
        if (!isset($_GET['account'])) {
            return show_error(404, 'Account ID missing');
        }

        if (!isset($_GET['attachment'])) {
            return show_error(404, 'Attachment ID missing');
        }

        return send_attachment($_GET['account'], $_GET['attachment']);

    default:
        show_error(404, 'Unknown action');
}
