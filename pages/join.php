<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

      <h2>Find All Student Numbers & Corresponding Room Numbers of Residents and RAs Assigned to the Given Room Type</h2>

      <hr />

      <form method="GET" action="join.php"> <!--refresh page when submitted-->
          <input type="hidden" id="Search" name="joinQueryRequest">

          Room Type: <select id="RoomTypes" name="insRoomTypes">
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Studio">Studio</option>
            <option value="Shared">Shared</option>
          </select> <br /><br />

          <input type="submit" value="Submit" name="joinSubmit"></p>
      </form>

      <hr />

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr);
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResultJoin($result, $RoomType) {
            echo "<br>All Student Numbers & Corresponding Room Numbers of Residents and RAs Assigned to $RoomType rooms:<br>";
            echo "<table>";
            echo "<tr><th>Student Numbers</th><th>Room Numbers</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
            }

            echo "</table>";
        }


        function handleJoinRequest() {
            global $db_conn;

            $RoomType =  $_GET['insRoomTypes'];

            $result = executePlainSQL(" SELECT re.StudentNumber, r.RoomNumber
                                        FROM AssignRE are, Resident re, Room r
                                        WHERE re.StudentNumber = are.StudentNumberRE AND
                                              are.Name = r.Name AND
                                              are.RoomNumber = r.RoomNumber AND
                                              are.Address = r.Address AND
                                              r.RoomType = '" . $RoomType . "'
                                        UNION
                                        SELECT ra.StudentNumber, r.RoomNumber
                                        FROM AssignRA ara, ResidenceAdvisor ra, Room r
                                        WHERE ra.StudentNumber = ara.StudentNumberRA AND
                                              ara.Name = r.Name AND
                                              ara.RoomNumber = r.RoomNumber AND
                                              ara.Address = r.Address AND
                                              r.RoomType = '" . $RoomType . "'");

            printResultJoin($result, $RoomType);
        }

        function devModeTable1() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM Resident");

          echo "<tr><th>Resident</th></tr>";

          echo "<tr><th>Student Number</th><th>Name</th><th>ContactInformation</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable2() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM AssignRE");

          echo "<tr><th>AssignRE</th></tr>";

          echo "<tr><th>Address</th><th>Room Number</th><th>Building Name</th><th>Student Number</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable3() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM ResidenceAdvisor");

          echo "<tr><th>Residence Advisor</th></tr>";

          echo "<tr><th>Student Number</th><th>Name</th><th>ContactInformation</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable4() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM AssignRA");

          echo "<tr><th>AssignRA</th></tr>";

          echo "<tr><th>Address</th><th>Room Number</th><th>Building Name</th><th>Student Number</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>";
          }

          disconnectFromDB();
        }


        function devModeTable5() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM Room");

          echo "<tr><th>Room</th></tr>";

          echo "<tr><th>Address</th><th>Building Name</th><th>Room Number</th><th>Room Type</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_zelongli", "a37718178", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('joinSubmit', $_GET)) {
                    handleJoinRequest();

                }
                disconnectFromDB();
            }
        }

      if ( isset($_GET['joinQueryRequest'])) {
            handleGETRequest();
      }
		?>

    <hr />

    <form action="../project.php">
      <input type="submit" value="Back" />
    </form>

    <form>
    <input type="button" id='devButton' value="Developer Mode ON" name="DEVMODE"></p>

    <table id='devTable1' style="display:initial;" style="float:left;">
      <?php devModeTable1();?>
    </table>

    <table id='devTable2' style="display:initial;" style="float:left;">
      <?php devModeTable2();?>
    </table>

    <table id='devTable3' style="display:initial;" style="float:left;">
      <?php devModeTable3();?>
    </table>

    <table id='devTable4' style="display:initial;" style="float:left;">
      <?php devModeTable4();?>
    </table>

    <table id='devTable5' style="display:initial;" style="float:left;">
      <?php devModeTable5();?>
    </table>

    <script>
      let button = document.getElementById('devButton');
      let table1 = document.getElementById('devTable1');
      let table2 = document.getElementById('devTable2');
      let table3 = document.getElementById('devTable3');
      let table4 = document.getElementById('devTable4');
      let table5 = document.getElementById('devTable5');

      button.addEventListener('click', buttonfn);

      function buttonfn() {
        if (button.value === 'Developer Mode OFF') {
          button.value = 'Developer Mode ON';
          table1.style.display = 'initial';
          table2.style.display = 'initial';
          table3.style.display = 'initial';
          table4.style.display = 'initial';
          table5.style.display = 'initial';
        } else {
          button.value = 'Developer Mode OFF';
          table1.style.display = 'none';
          table2.style.display = 'none';
          table3.style.display = 'none';
          table4.style.display = 'none';
          table5.style.display = 'none';
        }
      }
    </script>
    </form>

	</body>
</html>
