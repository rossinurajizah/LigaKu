<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof']) && isset($_POST['payment_id'])) {
    $payment_id = intval($_POST['payment_id']);
    $file = $_FILES['proof'];
    $upload_dir = 'uploads/payment_proofs/';
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), $allowed_ext)) {
        $new_filename = 'proof_' . $payment_id . '_' . time() . '.' . $ext;
        $destination = $upload_dir . $new_filename;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $conn->query("UPDATE payments SET payment_proof='$new_filename' WHERE payment_id=$payment_id");
        }
    }
}

header("Location: konfirmasi_pembayaran.php");
exit();
