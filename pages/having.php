<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

      <h2>Find Room Types Whose Residents' Average Age is Greater Than the Specified Age</h2>

      <hr />

      <form method="GET" action="having.php"> <!--refresh page when submitted-->
          <input type="hidden" id="havingQueryRequest" name="havingQueryRequest">

          Age: <input type="number" name="insAge"> <br /><br />

          <input type="submit" value="Submit" name="havingSubmit"></p>
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

        function printResultHaving($result, $age) {
          echo "<br>Find Room Types Whose Residents' Average Age is Greater Than $age<br>";
          echo "<table>";
          echo "<tr><th>Room Type</th><th>Average Age</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . round($row[1], 2) . "</td></tr>";
          }

          echo "</table>";
        }

        function handleHavingRequest() {
            global $db_conn;

            $age =  $_GET['insAge'];

            if ($age != NULL && $age > 0 && $age < 150) {
              $result = executePlainSQL("SELECT r.RoomType, AVG(re.Age)
                                         FROM Room r, AssignRE are, Resident re
                                         WHERE r.RoomNumber = are.RoomNumber AND
                                               r.Address = are.Address  AND
                                               r.Name = are.Name AND
                                               re.StudentNumber = are.StudentNumberRE
                                         GROUP BY r.RoomType
                                         HAVING AVG(re.Age) > $age");
              printResultHaving($result, $age);
            } else {
              if ($age == NULL) {
                echo "Age Cannot Be Empty";
              } else {
                echo "Age Must Be Between 0 to 150 (Exclusive)";
              }
            }
        }

        function devModeTable() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM Resident");

          echo "<tr><th>Resident</th></tr>";

          echo "<tr><th>Student Number</th><th>Name</th><th>ContactInformation</th><th>PreferenceList</th><th>Age</th><th>University</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td><td>" . $row[5] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable1() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM AssignRE");

          echo "<tr><th>AssignRE</th></tr>";

          echo "<tr><th>Address</th><th>Room Number</th><th>Building Name</th><th>Student Number</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable2() {

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
                if (array_key_exists('havingSubmit', $_GET)) {
                    handleHavingRequest();

                }
                disconnectFromDB();
            }
        }

      if ( isset($_GET['havingQueryRequest'])) {
            handleGETRequest();
      }
		?>

    <hr />

    <form action="../project.php">
      <input type="submit" value="Back" />
    </form>

    <hr />

    <form>
    <input type="button" id='devButton' value="Developer Mode ON" name="DEVMODE"></p>
    <table id='devTable' style="display:initial;" style="float:left;">
      <?php devModeTable();?>
    </table>

    <table id='devTable1' style="display:initial;" style="float:left;">
      <?php devModeTable1();?>
    </table>

    <table id='devTable2' style="display:initial;" style="float:left;">
      <?php devModeTable2();?>
    </table>

    <script>
      let button = document.getElementById('devButton');
      let table = document.getElementById('devTable');
      let table1 = document.getElementById('devTable1');
      let table2 = document.getElementById('devTable2');

      button.addEventListener('click', buttonfn);

      function buttonfn() {
        if (button.value === 'Developer Mode OFF') {
          button.value = 'Developer Mode ON';
          table.style.display = 'initial';
          table1.style.display = 'initial';
          table2.style.display = 'initial';
        } else {
          button.value = 'Developer Mode OFF';
          table.style.display = 'none';
          table1.style.display = 'none';
          table2.style.display = 'none';
        }
      }
    </script>
    </form>

	</body>
</html>
