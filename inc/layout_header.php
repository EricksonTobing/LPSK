<!DOCTYPE html>
<html lang="id" x-data :class="{ 'dark': $store.darkMode.on }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($title ?? 'LPSK App') ?></title>
  
<link rel="stylesheet" href="<?= base_url('assets/css/build.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">

   <!-- <link rel="stylesheet" href="/assets/css/build.css"> -->
<!-- <link rel="stylesheet" href="/assets/css/custom.css"> -->
  
  <!-- AlpineJS -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
      <!-- Box Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <!-- Icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200 flex flex-col min-h-screen">
  