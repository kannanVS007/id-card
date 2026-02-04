<?php require_once 'auth_check.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $logoPath = $uploadDir . 'logo.png';
    
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
        $message = "✅ Logo uploaded successfully!";
        $success = true;
    } else {
        $message = "❌ Failed to upload logo";
        $success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload School Logo</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-2xl mx-auto">
  
  <div class="bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-3xl font-bold text-teal-600 mb-6">Upload School Logo</h1>
    
    <?php if (isset($message)): ?>
    <div class="mb-6 p-4 rounded-lg <?= $success ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' ?>">
      <p class="font-semibold <?= $success ? 'text-green-800' : 'text-red-800' ?>"><?= $message ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Current Logo -->
    <?php if (file_exists('uploads/logo.png')): ?>
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
      <h3 class="font-bold text-gray-800 mb-3">Current Logo:</h3>
      <img src="uploads/logo.png?<?= time() ?>" alt="Current Logo" class="max-w-xs border-2 border-gray-300 rounded">
    </div>
    <?php endif; ?>
    
    <!-- Upload Form -->
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Logo File (PNG recommended)</label>
        <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg" required
               class="w-full border-2 border-gray-300 rounded-lg p-2 text-sm focus:border-teal-500">
        <p class="text-xs text-gray-500 mt-1">Recommended: PNG with transparent background, 200x200px or larger</p>
      </div>
      
      <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-lg transition">
        Upload Logo
      </button>
    </form>
    
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
      <h3 class="font-bold text-blue-900 mb-2">💡 Tips:</h3>
      <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
        <li>Use PNG format with transparent background for best results</li>
        <li>Recommended size: 200x200 pixels minimum</li>
        <li>Logo will be automatically resized to fit the ID card</li>
        <li>After uploading, refresh your ID card pages to see the new logo</li>
      </ul>
    </div>
    
    <div class="mt-6 flex gap-4">
      <a href="index.php" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 rounded-lg text-center transition">
        ← Back to Main
      </a>
      <a href="test_12_students.php" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg text-center transition">
        View Test Cards
      </a>
    </div>
  </div>

</div>

</body>
</html>