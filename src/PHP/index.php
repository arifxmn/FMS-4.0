<!DOCTYPE HTML>
<html>
  <head>
    <title>FARM 4.0 DASHBOARD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="icon" href="data:,">
    <style>
      html { font-family: Sans-serif; display: inline-block; text-align: center; }
      p { font-size: 1.2rem; }
      h4 { font-size: 0.8rem; }
      body { margin: 0; }
      .topnav { overflow: hidden; background-color: #0c6980; color: white; font-size: 1.2rem; }
      .content { padding: 5px; }
      .card { background-color: white; box-shadow: 0px 0px 10px 1px rgba(140,140,140,.5); border: 1px solid #0c6980; border-radius: 15px; }
      .card.header { background-color: #0c6980; color: white; border-bottom-right-radius: 0px; border-bottom-left-radius: 0px; border-top-right-radius: 12px; border-top-left-radius: 12px; }
      .cards { max-width: 700px; margin: 0 auto; display: grid; grid-gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
      .reading { font-size: 1.3rem; }
      .packet { color: #bebebe; }
      .temperatureColor { color: #fd7e14; }
      .humidityColor { color: #1b78e2; }
      .statusreadColor { color: #702963; font-size:12px; }
      .LEDColor { color: #183153;}
      
      .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
      }

      .switch input { display:none; }

      .sliderTS {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #D3D3D3;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 34px;
      }

      .sliderTS:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: #f7f7f7;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 50%;
      }

      input:checked + .sliderTS {
        background-color: #00878F;
      }

      input:focus + .sliderTS {
        box-shadow: 0 0 1px #2196F3;
      }

      input:checked + .sliderTS:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
      }

      .sliderTS:after {
        content:'OFF';
        color: white;
        display: block;
        position: absolute;
        transform: translate(-50%,-50%);
        top: 50%;
        left: 70%;
        font-size: 10px;
        font-family: Verdana, sans-serif;
      }

      input:checked + .sliderTS:after {  
        left: 25%;
        content:'ON';
      }

      input:disabled + .sliderTS {  
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
      }
    </style>
  </head>
  
  <body>
    <div class="topnav">
      <h3>FARM 4.0 DASHBOARD</h3>
    </div>
    
    <br>
    
    <div class="content">
      <div class="cards">
        <div class="card">
          <div class="card header">
            <h3 style="font-size: 1rem;">MONITORING</h3>
          </div>
          
          <h4 class="temperatureColor"><i class="fas fa-thermometer-half"></i> TEMPERATURE</h4>
          <p class="temperatureColor"><span class="reading"><span id="ESP32_Temp"></span> &deg;C</span></p>
          <h4 class="humidityColor"><i class="fas fa-tint"></i> HUMIDITY</h4>
          <p class="humidityColor"><span class="reading"><span id="ESP32_Humd"></span> &percnt;</span></p>
          
          <p class="statusreadColor"><span>DHT11 Sensor Status : </span><span id="ESP32_Sensor_Status"></span></p>
        </div>
        
        <div class="card">
          <div class="card header">
            <h3 style="font-size: 1rem;">CONTROLLING</h3>
          </div>
          
          <h4 class="LEDColor"><i class="fas fa-lightbulb"></i> LED-1</h4>
          <label class="switch">
            <input type="checkbox" id="ESP32_TogLED_01" onclick="GetTogBtnLEDState('ESP32_TogLED_01')">
            <div class="sliderTS"></div>
          </label>
          <h4 class="LEDColor"><i class="fas fa-lightbulb"></i> LED-2</h4>
          <label class="switch">
            <input type="checkbox" id="ESP32_TogLED_02" onclick="GetTogBtnLEDState('ESP32_TogLED_02')">
            <div class="sliderTS"></div>
          </label>
        </div>       
      </div>
    </div>
    
    <br>
    
    <div class="content">
      <div class="cards">
        <div class="card header" style="border-radius: 15px;">
            <h3 style="font-size: 0.7rem;">LAST TIME RECEIVED DATA [ <span id="ESP32_LTRD"></span> ]</h3>
            <button onclick="window.open('recordTable.php', '_blank');">Open Record Table</button>
            <h3 style="font-size: 0.7rem;"></h3>
        </div>
      </div>
    </div>
    
    <script>
      document.getElementById("ESP32_Temp").innerHTML = "NN"; 
      document.getElementById("ESP32_Humd").innerHTML = "NN";
      document.getElementById("ESP32_Sensor_Status").innerHTML = "NN";
      document.getElementById("ESP32_LTRD").innerHTML = "NN";
      
      Get_Data("ESP32");
      
      setInterval(myTimer, 5000);
      
      function myTimer() {
        Get_Data("ESP32");
      }
      
      function Get_Data(id) {
				if (window.XMLHttpRequest) {
          xmlhttp = new XMLHttpRequest();
        } else {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            const myObj = JSON.parse(this.responseText);
            if (myObj.id == "ESP32") {
              document.getElementById("ESP32_Temp").innerHTML = myObj.temperature;
              document.getElementById("ESP32_Humd").innerHTML = myObj.humidity;
              document.getElementById("ESP32_Sensor_Status").innerHTML = myObj.sensor_status;
              document.getElementById("ESP32_LTRD").innerHTML = "Time : " + myObj.ls_time + " | Date : " + myObj.ls_date + " (dd-mm-yyyy)";
              if (myObj.LED_01 == "ON") {
                document.getElementById("ESP32_TogLED_01").checked = true;
              } else if (myObj.LED_01 == "OFF") {
                document.getElementById("ESP32_TogLED_01").checked = false;
              }
              if (myObj.LED_02 == "ON") {
                document.getElementById("ESP32_TogLED_02").checked = true;
              } else if (myObj.LED_02 == "OFF") {
                document.getElementById("ESP32_TogLED_02").checked = false;
              }
            }
          }
        };
        xmlhttp.open("POST","getData.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("id="+id);
			}
      
      function GetTogBtnLEDState(togbtnid) {
        if (togbtnid == "ESP32_TogLED_01") {
          var togbtnchecked = document.getElementById(togbtnid).checked;
          var togbtncheckedsend = "";
          if (togbtnchecked == true) togbtncheckedsend = "ON";
          if (togbtnchecked == false) togbtncheckedsend = "OFF";
          controlActuators("ESP32","LED_01",togbtncheckedsend);
        }
        if (togbtnid == "ESP32_TogLED_02") {
          var togbtnchecked = document.getElementById(togbtnid).checked;
          var togbtncheckedsend = "";
          if (togbtnchecked == true) togbtncheckedsend = "ON";
          if (togbtnchecked == false) togbtncheckedsend = "OFF";
          controlActuators("ESP32","LED_02",togbtncheckedsend);
        }
      }
      
      function controlActuators(id,lednum,ledstate) {
				if (window.XMLHttpRequest) {
          xmlhttp = new XMLHttpRequest();
        } else {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
          }
        }
        xmlhttp.open("POST","controlActuators.php",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("id="+id+"&lednum="+lednum+"&ledstate="+ledstate);
			}
      //------------------------------------------------------------
    </script>
  </body>
</html>