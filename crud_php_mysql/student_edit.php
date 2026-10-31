<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

// Fungsi pembersih input (double_encode=false agar tidak ter-encode dua kali)
function clean($value) {
    return htmlentities(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8', false);
}

$arr_month = [
    "1"=>"Januari","2"=>"Februari","3"=>"Maret","4"=>"April",
    "5"=>"Mei","6"=>"Juni","7"=>"Juli","8"=>"Agustus",
    "9"=>"September","10"=>"Oktober","11"=>"Nopember","12"=>"Desember"
];
$error_message = "";

if (isset($_POST["submit"])) {
    // ====== PROSES UPDATE ======
    $nim         = clean($_POST["nim"]);
    $name        = clean($_POST["name"]);
    $birth_city  = clean($_POST["birth_city"]);
    $faculty     = clean($_POST["faculty"]);
    $department  = clean($_POST["department"]);
    $gpa         = clean($_POST["gpa"]);
    $birth_date  = clean($_POST["birth_date"]);
    $birth_month = clean($_POST["birth_month"]);
    $birth_year  = clean($_POST["birth_year"]);

    if (empty($name))       $error_message .= "- Nama belum diisi <br>";
    if (empty($birth_city)) $error_message .= "- Tempat lahir belum diisi <br>";
    if (empty($department)) $error_message .= "- Jurusan belum diisi <br>";
    if (!is_numeric($gpa) OR ($gpa <= 0) OR ($gpa > 4)) {
        $error_message .= "- IPK harus diisi dengan angka antara 0 - 4 <br>";
    }

    if ($error_message === "") {
        $nim_db        = mysqli_real_escape_string($connection, $nim);
        $name_db       = mysqli_real_escape_string($connection, $name);
        $city_db       = mysqli_real_escape_string($connection, $birth_city);
        $faculty_db    = mysqli_real_escape_string($connection, $faculty);
        $department_db = mysqli_real_escape_string($connection, $department);
        $gpa_db        = (float) $gpa;
        $birth_full    = (int)$birth_year."-".(int)$birth_month."-".(int)$birth_date;

        $query  = "UPDATE student SET ";
        $query .= "name='$name_db', birth_city='$city_db', ";
        $query .= "birth_date='$birth_full', faculty='$faculty_db', ";
        $query .= "department='$department_db', gpa=$gpa_db ";
        $query .= "WHERE nim='$nim_db'";

        $result = mysqli_query($connection, $query);
        if ($result) {
            $message = "Data mahasiswa dengan nama = \"<b>$name</b>\" berhasil diubah";
            header("Location: student_view.php?message=".urlencode($message));
            die();
        } else {
            die("Query gagal dijalankan: ".mysqli_errno($connection)." - ".mysqli_error($connection));
        }
    }
} else {
    // ====== AMBIL DATA LAMA DARI DATABASE ======
    if (!isset($_GET["nim"])) {
        header("Location: student_view.php");
        die();
    }
    $nim = mysqli_real_escape_string($connection, clean($_GET["nim"]));
    $result = mysqli_query($connection, "SELECT * FROM student WHERE nim='$nim'");
    if (!$result) {
        die("Query Error: ".mysqli_errno($connection)." - ".mysqli_error($connection));
    }
    if (mysqli_num_rows($result) == 0) {
        header("Location: student_view.php?message=".urlencode("Data mahasiswa tidak ditemukan"));
        die();
    }
    $data        = mysqli_fetch_assoc($result);
    $nim         = $data["nim"];
    $name        = $data["name"];
    $birth_city  = $data["birth_city"];
    $faculty     = $data["faculty"];
    $department  = $data["department"];
    $gpa         = $data["gpa"];
    $ts          = strtotime($data["birth_date"]);
    $birth_date  = (int) date("j", $ts);
    $birth_month = (int) date("n", $ts);
    $birth_year  = (int) date("Y", $ts);
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Data Mahasiswa</title>
<link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <div id="header"><h1 id="logo">Data Mahasiswa</h1></div>
  <hr>
  <nav>
    <ul>
      <li><a href="student_view.php">Tampil</a></li>
      <li><a href="student_add.php">Tambah</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>

  <h2>Edit Data Mahasiswa</h2>
  <?php if ($error_message !== "") echo "<div class='error'>$error_message</div>"; ?>

  <form id="form_mahasiswa" action="student_edit.php" method="post">
    <fieldset>
      <legend>Edit Mahasiswa</legend>
      <p>
        <label for="nim">NIM : </label>
        <input type="text" id="nim" value="<?php echo $nim ?>" disabled>
        <input type="hidden" name="nim" value="<?php echo $nim ?>">
      </p>
      <p>
        <label for="name">Nama : </label>
        <input type="text" name="name" id="name" value="<?php echo $name ?>">
      </p>
      <p>
        <label for="birth_city">Tempat Lahir : </label>
        <input type="text" name="birth_city" id="birth_city" value="<?php echo $birth_city ?>">
      </p>
      <p>
        <label for="birth_date">Tanggal Lahir : </label>
        <select name="birth_date" id="birth_date">
          <?php
          for ($i = 1; $i <= 31; $i++) {
              $sel = ($i == $birth_date) ? "selected" : "";
              echo "<option value='$i' $sel>".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>";
          }
          ?>
        </select>
        <select name="birth_month">
          <?php
          foreach ($arr_month as $key => $value) {
              $sel = ($key == $birth_month) ? "selected" : "";
              echo "<option value='$key' $sel>$value</option>";
          }
          ?>
        </select>
        <select name="birth_year">
          <?php
          for ($i = 1990; $i <= 2005; $i++) {
              $sel = ($i == $birth_year) ? "selected" : "";
              echo "<option value='$i' $sel>$i</option>";
          }
          ?>
        </select>
      </p>
      <p>
        <label for="faculty">Fakultas : </label>
        <select name="faculty" id="faculty">
          <option value="FTIB"  <?php if ($faculty == "FTIB")  echo "selected" ?>>FTIB</option>
          <option value="FTEIC" <?php if ($faculty == "FTEIC") echo "selected" ?>>FTEIC</option>
        </select>
      </p>
      <p>
        <label for="department">Jurusan : </label>
        <input type="text" name="department" id="department" value="<?php echo $department ?>">
      </p>
      <p>
        <label for="gpa">IPK : </label>
        <input type="text" name="gpa" id="gpa" value="<?php echo $gpa ?>" placeholder="Contoh: 2.75">
        (angka desimal dipisah dengan karakter titik ".")
      </p>
    </fieldset>
    <br>
    <p><input type="submit" name="submit" value="Simpan Perubahan"></p>
  </form>
</div>
</body>
</html>
<?php mysqli_close($connection); ?>