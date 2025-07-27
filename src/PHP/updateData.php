<?php
  require 'database.php';
  
  if (!empty($_POST)) {
    $id = $_POST['id'];
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $sensor_status = $_POST['sensor_status'];
    $led_01 = $_POST['led_01'];
    $led_02 = $_POST['led_02'];
    
    date_default_timezone_set("Asia/Dhaka");
    $tm = date("H:i:s");
    $dt = date("Y-m-d");
    
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "UPDATE system_state SET temperature = ?, humidity = ?, sensor_status = ?, time = ?, date = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($temperature,$humidity,$sensor_status,$tm,$dt,$id));
    Database::disconnect();
    
    $id_key;
    $board = $_POST['id'];
    $found_empty = false;
    
    $pdo = Database::connect();
    
    while ($found_empty == false) {
      $id_key = generate_string_id(10);
      $sql = 'SELECT * FROM system_records WHERE id="' . $id_key . '"';
      $q = $pdo->prepare($sql);
      $q->execute();
      
      if (!$data = $q->fetch()) {
        $found_empty = true;
      }
    }
    
    //:::::::: The process of entering data into the record table.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "INSERT INTO system_records (id,board,temperature,humidity,sensor_status,LED_01,LED_02,time,date) values(?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$q = $pdo->prepare($sql);
		$q->execute(array($id_key,$board,$temperature,$humidity,$sensor_status,$led_01,$led_02,$tm,$dt));
    
    Database::disconnect();
  }
  
  //---------------------------------------- Function to create "id" based on numbers and characters.
  function generate_string_id($strength = 16) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $input_length = strlen($permitted_chars);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
      $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
      $random_string .= $random_character;
    }
    return $random_string;
  }
?>