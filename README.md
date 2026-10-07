# Network Topology Studio

A PHP 8+ and vanilla JavaScript topology editor. No database, Composer packages, or JavaScript dependencies are required.

## Run

```sh
cd /workspace/my_repository
php -S 127.0.0.1:8080 -t .
```

Open the server in your local browser when running on your own machine. The PHP built-in server is for development.

## Features

- Add routers, switches, servers, and computers.
- Drag devices to arrange the topology.
- Toggle **Connect devices**, then select two devices.
- Click a connection to remove it; select a device and use **Delete selected device** to remove it.
- Save to this browser's local storage, or export/import JSON files.
- PHP validates imported and exported documents, IDs, types, coordinates, and connection references.
- Maximum 200 devices, 1000 connections, and 1 MB per document.

Local saves are browser-specific. Export a JSON backup before clearing browser storage. This editor designs diagrams; it does not discover or configure real network devices.

## Files

- `index.php`: UI and JSON validation endpoint (`POST index.php?api=validate`).
- `assets/app.js`: SVG editor and browser storage.
- `assets/style.css`: responsive layout.

## Checks

```sh
php -l index.php
node --check assets/app.js
```

## Standalone HTML version

Open `index.html` directly in your browser. CSS and JavaScript are embedded, so PHP and a web server are unnecessary. This version validates JSON locally and has the same editor features. Browser local-storage behavior for file URLs varies; use Export JSON for reliable backups.
