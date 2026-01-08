import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

const manifestPath = join(process.cwd(), 'public/build/manifest.json');
const indexPath = join(process.cwd(), 'public/build/index.html');

try {
  const manifest = JSON.parse(readFileSync(manifestPath, 'utf-8'));
  
  // Get CSS files from manifest (only CSS files)
  const cssFiles = Object.values(manifest)
    .filter(entry => entry.file && entry.file.endsWith('.css'))
    .map(entry => `/build/${entry.file}`);
  
  // Get JS files from manifest (only JS files)
  const jsFiles = Object.values(manifest)
    .filter(entry => entry.file && entry.file.endsWith('.js'))
    .map(entry => `/build/${entry.file}`);
  
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light dark">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' data: gap: https://ssl.gstatic.com 'unsafe-eval' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; script-src 'self' 'unsafe-inline' 'unsafe-eval'; connect-src 'self' https://serviceman.sekimbi.com https://api.paystack.co wss://serviceman.sekimbi.com; font-src 'self' https://fonts.bunny.net; img-src 'self' data: https:;">
    <title>ServiceMan</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    ${cssFiles.map(css => `    <link rel="stylesheet" href="${css}">`).join('\n')}
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        #app {
            width: 100%;
            height: 100vh;
        }
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="loading">Loading ServiceMan...</div>
    </div>
    
    ${jsFiles.map(js => `    <script type="module" src="${js}"></script>`).join('\n')}
</body>
</html>`;

  writeFileSync(indexPath, html, 'utf-8');
  console.log('✅ Mobile index.html generated successfully');
} catch (error) {
  console.error('❌ Error generating mobile index.html:', error.message);
  process.exit(1);
}

