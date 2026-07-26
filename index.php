<?php
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedTypes = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
    'video' => ['mp4', 'webm', 'mov'],
];

$mediaFiles = [];

// Scan uploaded files and categorize them
foreach (scandir($uploadDir) as $file) {
    if ($file === '.' || $file === '..') continue;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    foreach ($allowedTypes as $type => $exts) {
        if (in_array($ext, $exts)) {
            $mediaFiles[$type][] = $file;
            break;
        }
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['upload'])) {
    $file = $_FILES['upload'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allExts = array_merge(...array_values($allowedTypes));

    if (!in_array($ext, $allExts)) {
        $message = "Unsupported file type.";
    } elseif ($file['size'] > 50_000_000) {
        $message = "File too large (max 50MB).";
    } elseif ($file['error'] === UPLOAD_ERR_OK) {
        $safeName = uniqid('media_', true) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $safeName);
        $message = "Upload successful!";
    } else {
        $message = "Upload failed with error code {$file['error']}.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Media Page</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: sans-serif; background: #1a1a2e; color: #eee;
         padding: 16px; max-width: 768px; margin: 0 auto; }
  h1 { font-size: 1.4rem; margin-bottom: 16px; }

  /* Upload form */
  .upload-form {
    background: #16213e; padding: 20px; border-radius: 12px;
    margin-bottom: 24px; text-align: center;
  }
  .upload-form label {
    display: block; cursor: pointer; padding: 12px 24px;
    background: #6d4aff; color: #fff; border-radius: 8px;
    font-weight: bold; margin-bottom: 8px;
  }
  .upload-form input[type="file"] { display: none; }
  .upload-form button {
    background: transparent; border: 1px solid #6d4aff; color: #fff;
    padding: 10px 20px; border-radius: 8px; cursor: pointer;
  }
  .message { color: #4ade80; margin-top: 8px; font-size: 0.9rem; }

  /* Media sections */
  section { margin-bottom: 28px; }
  section h2 { font-size: 1.1rem; margin-bottom: 12px; }

  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }
  .media-item {
    background: #16213e; border-radius: 12px; overflow: hidden;
  }
  img { width: 100%; height: auto; display: block; }
  video { width: 100%; border-radius: 8px; }
  audio { width: 100%; margin: 4px 0; }
  .caption { padding: 6px 8px; font-size: 0.75rem; color: #aaa;
             word-break: break-all; }
</style>
</head>
<body>

<h1>📱 My Media Gallery</h1>

<div class="upload-form">
  <form method="POST" enctype="multipart/form-data">
    <label for="fileInput">📎 Choose Photo / Audio / Video</label>
    <input id="fileInput" type="file" name="upload"
           accept="image/*,audio/*,video/*" capture>
    <button type="submit">Upload</button>
  </form>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>
</div>

<?php foreach (['image' => 'Photos', 'audio' => 'Audio', 'video' => 'Videos'] as $type => $label):
    if (!empty($mediaFiles[$type])): ?>
    <section>
      <h2><?= $label ?></h2>
      <div class="grid">
        <?php foreach (array_reverse($mediaFiles[$type]) as $file): ?>
          <div class="media-item">
            <?php
            $url = 'uploads/' . rawurlencode($file);
            if ($type === 'image'): ?>
              <img src="<?= $url ?>" loading="lazy" alt="<?= htmlspecialchars($file) ?>">
            <?php elseif ($type === 'audio'): ?>
              <audio controls preload="metadata"><source src="<?= $url ?>"></audio>
            <?php else: ?>
              <video controls preload="metadata"><source src="<?= $url ?>"></video>
            <?php endif; ?>
            <div class="caption"><?= htmlspecialchars($file) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif;
endforeach; ?>

</body>
</html>