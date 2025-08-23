<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/helpers.php';
require_login();

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$fmt = strtolower((string)($_GET['fmt'] ?? 'xlsx')); // xlsx|csv|pdf
$t = $_GET['t'] ?? ($_GET['t'] ?? ''); // untuk table.php sudah ada t, untuk keuangan set t=keuangan
$tables = require __DIR__ . '/../inc/table_meta.php';

function fetchData(string $t, array $tables): array {
  if ($t==='keuangan') {
    $q = trim((string)($_GET['q'] ?? ''));
    $where = $q ? "WHERE (p.nomor_kuintasi LIKE :q OR p.kode_anggaran LIKE :q OR a.nama_anggaran LIKE :q OR p.keterangan LIKE :q)" : '';
    $sql = "SELECT p.tanggal, p.nomor_kuintasi, p.kode_anggaran, a.nama_anggaran, p.jumlah, p.keterangan
            FROM pengeluaran p LEFT JOIN anggaran a ON a.kode_anggaran=p.kode_anggaran $where ORDER BY p.tanggal DESC";
    $stmt = db()->prepare($sql);
    if ($q) $stmt->bindValue(':q', "%$q%");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    $headers = ['Tanggal','No Kuintasi','Kode Anggaran','Nama Anggaran','Jumlah','Keterangan'];
    return [$headers, $rows];
  }

  if (!isset($tables[$t])) { http_response_code(400); exit('Bad Request'); }
  $meta = $tables[$t];
  $searchable = $meta['searchable'] ?? [];
  $filters = $meta['filters'] ?? [];

  $where = [];
  $params = [];
  $q = trim((string)($_GET['q'] ?? ''));
  if ($q !== '' && $searchable) {
    $like = "%$q%";
    $parts = [];
    foreach ($searchable as $s) { $parts[] = "$s LIKE ?"; $params[]=$like; }
    $where[] = '(' . implode(' OR ', $parts) . ')';
  }
  foreach ($filters as $f) {
    if ($val = trim((string)($_GET[$f] ?? ''))) { $where[] = "$f = ?"; $params[]=$val; }
  }
  $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';
  $stmt = db()->prepare("SELECT * FROM $t $whereSql ORDER BY 1 DESC");
  $stmt->execute($params);
  $rows = $stmt->fetchAll();
  $headers = array_keys($rows[0] ?? array_flip($meta['columns']));
  return [$headers, $rows];
}

[$headers, $rows] = fetchData($t ?: ($_GET['t'] ?? ''), $tables);
$filename = ($t ?: 'data').'-'.date('Ymd-His');

if ($fmt === 'xlsx' || $fmt === 'csv') {
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $colIdx = 1;
  foreach ($headers as $h) { $sheet->setCellValueByColumnAndRow($colIdx++, 1, $h); }
  $ridx = 2;
  foreach ($rows as $row) {
    $colIdx = 1;
    foreach ($headers as $h) {
      $sheet->setCellValueByColumnAndRow($colIdx++, $ridx, $row[$h] ?? '');
    }
    $ridx++;
  }
  if ($fmt === 'xlsx') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename.xlsx\"");
    (new Xlsx($spreadsheet))->save('php://output');
  } else {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename.csv\"");
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
      fputcsv($out, array_map(fn($h)=>$row[$h] ?? '', $headers));
    }
    fclose($out);
  }
  exit;
}

if ($fmt === 'pdf') {
  ob_start();
  echo '<h3 style="font-family: sans-serif;">Laporan '.htmlspecialchars($t ?: 'Data').'</h3>';
  echo '<table width="100%" border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;font-size:12px;font-family:sans-serif">';
  echo '<tr style="background:#eee">';
  foreach ($headers as $h) echo '<th>'.htmlspecialchars((string)$h).'</th>';
  echo '</tr>';
  foreach ($rows as $row) {
    echo '<tr>';
    foreach ($headers as $h) echo '<td>'.htmlspecialchars((string)($row[$h] ?? '')).'</td>';
    echo '</tr>';
  }
  echo '</table>';
  $html = ob_get_clean();

  $dompdf = new Dompdf();
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'landscape');
  $dompdf->render();
  header('Content-Type: application/pdf');
  header("Content-Disposition: attachment; filename=\"$filename.pdf\"");
  echo $dompdf->output();
  exit;
}

http_response_code(400);
echo 'Format tidak didukung';
