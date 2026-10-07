<?php
declare(strict_types=1);
// Topology documents are supplied by the browser; no server-side shared storage.
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); header('Allow: POST');
        echo json_encode(['error' => 'Use POST']); exit;
    }
    try {
        $raw = file_get_contents('php://input', false, null, 0, 1048577);
        if (strlen($raw) > 1048576) throw new InvalidArgumentException('Document exceeds 1 MB');
        $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['nodes'], $data['links']) || !is_array($data['nodes']) || !is_array($data['links'])) throw new InvalidArgumentException('Invalid topology document');
        if (count($data['nodes']) > 200 || count($data['links']) > 1000) throw new InvalidArgumentException('Maximum: 200 devices and 1000 connections');
        $ids = []; $nodes = []; $links = []; $pairs = [];
        foreach ($data['nodes'] as $node) {
            if (!is_array($node) || !isset($node['id'], $node['name'], $node['type'], $node['x'], $node['y']) || !is_string($node['id']) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $node['id']) || isset($ids[$node['id']]) || !is_string($node['name']) || strlen($node['name']) < 1 || strlen($node['name']) > 120 || !in_array($node['type'], ['router','switch','server','computer'], true) || !is_numeric($node['x']) || !is_numeric($node['y']) || !is_finite((float)$node['x']) || !is_finite((float)$node['y']) || $node['x'] < 35 || $node['x'] > 965 || $node['y'] < 35 || $node['y'] > 565) throw new InvalidArgumentException('Invalid device');
            $ids[$node['id']] = true;
            $nodes[] = ['id'=>$node['id'], 'name'=>$node['name'], 'type'=>$node['type'], 'x'=>(float)$node['x'], 'y'=>(float)$node['y']];
        }
        foreach ($data['links'] as $link) {
            if (!is_array($link) || !isset($link['from'], $link['to']) || !is_string($link['from']) || !is_string($link['to']) || !isset($ids[$link['from']], $ids[$link['to']]) || $link['from'] === $link['to']) throw new InvalidArgumentException('Invalid connection');
            $pair = [$link['from'], $link['to']]; sort($pair); $key = implode(':', $pair);
            if (isset($pairs[$key])) throw new InvalidArgumentException('Duplicate connection');
            $pairs[$key] = true; $links[] = ['from'=>$link['from'], 'to'=>$link['to']];
        }
        echo json_encode(['nodes'=>$nodes,'links'=>$links], JSON_THROW_ON_ERROR);
    } catch (JsonException | InvalidArgumentException $e) {
        http_response_code(400); echo json_encode(['error'=>$e->getMessage()]);
    }
    exit;
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Network Topology Studio</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header><div><span class="eyebrow">INFRASTRUCTURE WORKSPACE</span><h1>Network Topology Studio</h1><p>Design your network. Connect your devices.</p></div><div class="actions"><button id="save">Save locally</button><button id="export">Export JSON</button><label class="button">Import JSON<input type="file" id="import" accept="application/json,.json" hidden></label></div></header>
<main><aside><h2>Add a device</h2><label>Device name<input id="name" maxlength="120" placeholder="e.g. Core Router"></label><label>Device type<select id="type"><option value="router">Router</option><option value="switch">Switch</option><option value="server">Server</option><option value="computer">Computer</option></select></label><button id="add" class="primary">+ Add device</button><hr><h2>Canvas tools</h2><button id="connect" aria-pressed="false">Connect devices</button><button id="delete" disabled>Delete selected device</button><button id="reset">Reset to sample</button><p class="help">Drag devices to move them. Select a device to delete it. In connection mode, click two devices. Click a connection to remove it.</p><p class="help">Local saves stay in this browser. Export JSON to share or back up your topology.</p></aside><section><div class="canvas-heading"><h2>Topology canvas</h2><span id="count"></span></div><svg id="canvas" viewBox="0 0 1000 600" role="img" aria-label="Interactive network topology"><defs><pattern id="grid" width="25" height="25" patternUnits="userSpaceOnUse"><circle cx="1" cy="1" r="1" fill="#cbd5e1"/></pattern></defs><rect width="1000" height="600" fill="url(#grid)"/><g id="links"></g><g id="nodes"></g></svg><p id="status" role="status" aria-live="polite">Ready.</p></section></main><script src="assets/app.js"></script></body></html>
