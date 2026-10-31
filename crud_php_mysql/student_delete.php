<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

if (isset($_GET["nim"])) {
    $nim = htmlentities(strip_tags(trim($_GET["nim"])));
    $nim = mysqli_real_escape_string($connection, $nim);

    // Cek data ada atau tidak
    $result = mysqli_query($connection, "SELECT name FROM student WHERE nim='$nim'");
    if (mysqli_num_rows($result) == 0) {
        $message = "Data mahasiswa tidak ditemukan";
    } else {
        $data = mysqli_fetch_assoc($result);
        $name = $data["name"];
        $delete = mysqli_query($connection, "DELETE FROM student WHERE nim='$nim'");
        if ($delete) {
            $message = "Mahasiswa dengan nama = \"<b>$name</b>\" berhasil dihapus";
        } else {
            die("Query gagal dijalankan: ".mysqli_errno($connection)." - ".mysqli_error($connection));
        }
    }
    mysqli_free_result($result);
} else {
    $message = "NIM tidak valid";
}
mysqli_close($connection);
header("Location: student_view.php?message=".urlencode($message));
die();
?>