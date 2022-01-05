<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

      <h2>Find Distinct Wages Below the Average Wage of Employees Who Earn Above the Overall Average Wage Among All Staff That Are Earned By More Than One Employee</h2>

      <hr />

      <form method="GET" action="nested.php"> <!--refresh page when submitted-->
          <input type="hidden" id="nestedQueryRequest" name="nestedQueryRequest">
          <input type="submit" value="Submit" name="nestedSubmit"></p>
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

        function handleNestedRequest() {
            global $db_conn;

            executePlainSQL("drop view tempWages");

            executePlainSQL("CREATE VIEW tempWages(Wages, SINum) as
                                SELECT Wages, SINum
                                FROM CleaningStaff
                                UNION
                                SELECT Wages, SINum
                                FROM DiningHallStaff
                                UNION
                                SELECT Wages, SINum
                                FROM FrontDeskStaff");


            $result = executePlainSQL("SELECT COUNT(*) as DW, Wages
                                       FROM ( SELECT Wages, SINum
                                              FROM  tempWages
                                              WHERE Wages < ( SELECT AVG(Wages)
                                                              FROM  ( SELECT Wages, SINum
                                                                      FROM  tempWages
                                                                      WHERE Wages > ( SELECT AVG(Wages)
                                                                                      FROM  tempWages))))
                                       GROUP BY Wages
                                       HAVING COUNT(*) > 1");


             echo "<table>";
             echo "<tr><th>Number of Employees</th><th>Wages</th></tr>";

             while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                 echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
             }

             echo "</table>";
        }

        function devModeTable() {

          connectToDB();

          $result = executePlainSQL(" SELECT SINum, Name, Wages
                                      FROM CleaningStaff
                                      UNION
                                      SELECT SINum, Name, Wages
                                      FROM DiningHallStaff
                                      UNION
                                      SELECT SINum, Name, Wages
                                      FROM FrontDeskStaff");

          echo "<tr><th>SIN</th><th>Name</th><th>Wages</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
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
                if (array_key_exists('nestedSubmit', $_GET)) {
                    handleNestedRequest();

                }
                disconnectFromDB();
            }
        }

      if ( isset($_GET['nestedQueryRequest'])) {
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
    <table id='devTable' style="display:initial;">
      <?php devModeTable();?>
    </table>


    <script>
      let button = document.getElementById('devButton');
      let table = document.getElementById('devTable');

      button.addEventListener('click', buttonfn);

      function buttonfn() {
        if (button.value === 'Developer Mode OFF') {
          button.value = 'Developer Mode ON';
          table.style.display = 'initial';
        } else {
          button.value = 'Developer Mode OFF';
          table.style.display = 'none';
        }
      }
    </script>
    </form>
	</body>
</html>
