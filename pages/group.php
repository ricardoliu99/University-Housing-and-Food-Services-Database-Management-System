<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

      <h2>Find the Number of Cleaning Staff or the Lowest/Highest/Average Wage of All Cleaning Staff for Each Building</h2>

      <hr />

      <form method="GET" action="group.php">
          <input type="hidden" id="groupQueryRequest" name="groupQueryRequest">

          Type: <select id="Types" name="insTypes">
            <option value="Min">Find the lowest wages among all cleaning staff for each building</option>
            <option value="Max">Find the highest wages among all cleaning staff for each building</option>
            <option value="Avg">Find the average wage of all cleaning staff for each building</option>
            <option value="Count">Find the number of cleaning staffs in each building</option>
          </select> <br /><br />

          <input type="submit" value="Submit" name="groupSubmit"></p>
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

        function printResultGroup($result, $operator) {
          if ($operator == "Min") {
            echo "<br>Lowest Wage of Cleaning Staff For Each Building:<br>";
            echo "<table>";
            echo "<tr><th>Lowest Wage</th><th>Building Name</th><th>Building Address</th></tr>";
          } else if ($operator == "Max") {
            echo "<br>Highest Wage of Cleaning Staff For Each Building:<br>";
            echo "<table>";
            echo "<tr><th>Highest Wage</th><th>Building Name</th><th>Building Address</th></tr>";
          } elseif ($operator == "Avg") {
            echo "<br>Average Wages of Cleaning Staff For Each Building:<br>";
            echo "<table>";
            echo "<tr><th>Average Wage</th><th>Building Name</th><th>Building Address</th></tr>";
          } else {
            echo "<br>Count the Number of Cleaning Staff For Each Building:<br>";
            echo "<table>";
            echo "<tr><th>Number of staff</th><th>Building Name</th><th>Building Address</th></tr>";
          }

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
          }

          echo "</table>";
        }


        function handleGroupRequest() {
            global $db_conn;

            $operator =  $_GET['insTypes'];
            $result;
            if ($operator == "Min") {
              $result = executePlainSQL("SELECT MIN(cs.Wages), b.Building_Name, b.Building_Address
                                          FROM CleaningStaff cs, Cleans c, Building b
                                          WHERE cs.SINum = c.SINum AND
                                                c.Address = b.Building_Address AND
                                                b.Building_Name = c.Name
                                          GROUP BY b.Building_Name, b.Building_Address");

            } else if ($operator == "Max") {
              $result = executePlainSQL("SELECT MAX(cs.Wages), b.Building_Name, b.Building_Address
                                          FROM CleaningStaff cs, Cleans c, Building b
                                          WHERE cs.SINum = c.SINum AND
                                                c.Address = b.Building_Address AND
                                                b.Building_Name = c.Name
                                          GROUP BY b.Building_Name, b.Building_Address");

            } else if ($operator == "Avg") {
              $result = executePlainSQL("SELECT AVG(cs.Wages), b.Building_Name, b.Building_Address
                                          FROM CleaningStaff cs, Cleans c, Building b
                                          WHERE cs.SINum = c.SINum AND
                                                c.Address = b.Building_Address AND
                                                b.Building_Name = c.Name
                                          GROUP BY b.Building_Name, b.Building_Address");
            }  else {
              $result = executePlainSQL("SELECT COUNT(cs.SINum), b.Building_Name, b.Building_Address
                                          FROM CleaningStaff cs, Cleans c, Building b
                                          WHERE cs.SINum = c.SINum AND
                                                c.Address = b.Building_Address AND
                                                b.Building_Name = c.Name
                                          GROUP BY b.Building_Name, b.Building_Address");
            }

            printResultGroup($result, $operator);
        }

        function devModeTable() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM CleaningStaff");

          echo "<tr><th>Cleaning Staff</th></tr>";

          echo "<tr><th>SIN</th><th>Wages</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable1() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM Cleans");

          echo "<tr><th>Cleans</th></tr>";

          echo "<tr><th>SIN</th><th>Address</th><th>Building Name</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
          }

          disconnectFromDB();
        }

        function devModeTable2() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM Building");

          echo "<tr><th>Building</th></tr>";

          echo "<tr><th>Building Name</th><th>Building Address</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
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
                if (array_key_exists('groupSubmit', $_GET)) {
                    handleGroupRequest();

                }
                disconnectFromDB();
            }
        }

      if ( isset($_GET['groupQueryRequest'])) {
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
