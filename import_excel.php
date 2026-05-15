<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Upload error.';
        echo json_encode($response);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'csv'])) {
        $response['message'] = 'Only .xlsx or .csv files allowed.';
        echo json_encode($response);
        exit;
    }

    $inserted = 0;
    $errors = [];

    if ($ext === 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $headers = fgetcsv($handle);
            if ($headers) {
                $headers = array_map('trim', $headers);
                $map = getColumnMap($headers);
                $stmt = $conn->prepare("INSERT INTO nzsupportlist (sup_id, status, user_name, device, support_person, start_date, description, remarks, deadline, remaining_days, end_date) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                while (($row = fgetcsv($handle)) !== false) {
                    $data = mapRow($row, $headers, $map);
                    if ($data) {
                        $data['remaining_days'] = calcRemainingDays($data['deadline']);
                        $stmt->bind_param("sssssssssss", $data['sup_id'], $data['status'], $data['user_name'], $data['device'], $data['support_person'], $data['start_date'], $data['description'], $data['remarks'], $data['deadline'], $data['remaining_days'], $data['end_date']);
                        if ($stmt->execute()) {
                            $inserted++;
                        } else {
                            $errors[] = 'Row failed: ' . implode(', ', $row);
                        }
                    }
                }
            }
            fclose($handle);
        }
    } else {
        require_once 'lib/SimpleXLSX.php';
        $xlsx = Shuchkin\SimpleXLSX::parse($file['tmp_name']);
        if ($xlsx) {
            $rows = $xlsx->rows();
            if (count($rows) > 0) {
                $headers = array_map('trim', $rows[0]);
                $map = getColumnMap($headers);
                $stmt = $conn->prepare("INSERT INTO nzsupportlist (sup_id, status, user_name, device, support_person, start_date, description, remarks, deadline, remaining_days, end_date) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $data = mapRow($row, $headers, $map);
                    if ($data) {
                        $data['remaining_days'] = calcRemainingDays($data['deadline']);
                        $stmt->bind_param("sssssssssss", $data['sup_id'], $data['status'], $data['user_name'], $data['device'], $data['support_person'], $data['start_date'], $data['description'], $data['remarks'], $data['deadline'], $data['remaining_days'], $data['end_date']);
                        if ($stmt->execute()) {
                            $inserted++;
                        } else {
                            $errors[] = 'Row ' . ($i + 1) . ' failed';
                        }
                    }
                }
            }
        } else {
            $response['message'] = 'Parse error: ' . Shuchkin\SimpleXLSX::parseError();
            echo json_encode($response);
            exit;
        }
    }

    $response['success'] = true;
    $response['message'] = "$inserted records imported successfully.";
    if (count($errors) > 0) {
        $response['message'] .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5));
    }
    echo json_encode($response);
    exit;
}

function getColumnMap($headers) {
    $map = [];
    $colMap = [
        'supid' => 'sup_id',
        'sup_id' => 'sup_id',
        'status' => 'status',
        'user name' => 'user_name',
        'username' => 'user_name',
        'user_name' => 'user_name',
        'device' => 'device',
        'support person' => 'support_person',
        'support_person' => 'support_person',
        'start date' => 'start_date',
        'start_date' => 'start_date',
        'description' => 'description',
        'remarks' => 'remarks',
        'dead line' => 'deadline',
        'deadline' => 'deadline',
        'dead line' => 'deadline',
        'remaining days' => 'remaining_days',
        'remaining_days' => 'remaining_days',
        'end date' => 'end_date',
        'end_date' => 'end_date',
    ];

    foreach ($headers as $i => $h) {
        $key = strtolower(trim($h));
        if (isset($colMap[$key])) {
            $map[$i] = $colMap[$key];
        }
    }
    return $map;
}

function mapRow($row, $headers, $map) {
    $data = [
        'sup_id' => '', 'status' => 'Pending', 'user_name' => '',
        'device' => '', 'support_person' => '', 'start_date' => '',
        'description' => '', 'remarks' => '', 'deadline' => '',
        'remaining_days' => 0, 'end_date' => ''
    ];

    foreach ($map as $colIdx => $dbField) {
        if (isset($row[$colIdx])) {
            $data[$dbField] = trim($row[$colIdx]);
        }
    }

    if (empty($data['sup_id']) && empty($data['user_name'])) {
        return null;
    }
    return $data;
}

function calcRemainingDays($deadline) {
    if (!$deadline) return 0;
    $dead = new DateTime($deadline);
    $today = new DateTime();
    return max(0, (int) $today->diff($dead)->days);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel - NZIT Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="overflow-x:hidden;">
    <?php require_once 'header.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-file-earmark-excel"></i> Import Data from Excel
                    </div>
                    <div class="card-body">
                        <div id="alertBox"></div>

                        <div class="alert alert-info">
                            <strong>Import Instructions:</strong>
                            <ul class="mb-0">
                                <li>Upload an <code>.xlsx</code> or <code>.csv</code> file</li>
                                <li>First row must contain column headers — system automatically detects and maps to database fields</li>
                                <li>Only <strong>SupID</strong> or <strong>User Name</strong> column is required; rest are optional</li>
                            </ul>
                            <hr>
                            <strong class="d-block mb-1">Database Column Mapping:</strong>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size:0.85rem;">
                                    <thead class="table-dark">
                                        <tr><th>Excel Header (case-insensitive)</th><th>Database Field</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>supid, sup_id</td><td><code>sup_id</code></td></tr>
                                        <tr><td>status</td><td><code>status</code></td></tr>
                                        <tr><td>user name, username, user_name</td><td><code>user_name</code></td></tr>
                                        <tr><td>device</td><td><code>device</code></td></tr>
                                        <tr><td>support person, support_person</td><td><code>support_person</code></td></tr>
                                        <tr><td>start date, start_date</td><td><code>start_date</code></td></tr>
                                        <tr><td>description</td><td><code>description</code></td></tr>
                                        <tr><td>remarks</td><td><code>remarks</code></td></tr>
                                        <tr><td>dead line, deadline</td><td><code>deadline</code></td></tr>
                                        <tr><td>remaining days, remaining_days</td><td><code>remaining_days</code></td></tr>
                                        <tr><td>end date, end_date</td><td><code>end_date</code></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <form id="importForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Select Excel File</label>
                                <input type="file" name="excel_file" class="form-control" accept=".xlsx,.csv" required>
                            </div>
                            <button type="submit" class="btn btn-success" id="importBtn">
                                <i class="bi bi-upload"></i> Import Data
                            </button>
                            <a href="nzsupportlist.php" class="btn btn-secondary">Cancel</a>
                        </form>

                        <div class="mt-3" id="progressBox" style="display:none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%">Importing...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = document.getElementById('importBtn');
        var alertBox = document.getElementById('alertBox');
        var progressBox = document.getElementById('progressBox');

        btn.disabled = true;
        progressBox.style.display = 'block';
        alertBox.innerHTML = '';

        fetch('import_excel.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            progressBox.style.display = 'none';
            btn.disabled = false;
            if (data.success) {
                alertBox.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            } else {
                alertBox.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(function() {
            progressBox.style.display = 'none';
            btn.disabled = false;
            alertBox.innerHTML = '<div class="alert alert-danger">Upload failed.</div>';
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'footer.php'; ?>
</body>
</html>
