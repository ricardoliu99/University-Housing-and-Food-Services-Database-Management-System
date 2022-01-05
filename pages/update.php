<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

      <h2>Update Resident Contact Information</h2>

      <hr />

      <form method="POST" action="update.php">
          <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
          <!-- Student Number: <input type="number" min="100000000" max="999999999" name="insStudentNumber"> <br /><br /> -->


          Student Number: <select id="StudentNumber" name="insStudentNumber">
              <?php
                $result = test();
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                  $name = $row[0];
                  echo "<option value='$name'>$name</option>";
                }
              ?>
          </select> <br /><br />

          New Contact Information: <input type="text" minlength="5" maxlength="45" name="insContactInformation"> <br /><br />


          <input type="submit" value="Submit" name="updateSubmit"></p>
      </form>

      <hr />

      <form action="../project.php">
        <input type="submit" value="Back" />
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

        function test() {
          connectToDB();

          $result = executePlainSQL("SELECT StudentNumber FROM Resident");

          disconnectFromDB();

          return $result;
        }


        function handleUpdateRequest() {
            global $db_conn;

            $StudentNumber =  $_POST['insStudentNumber'];
            $ContactInformation = $_POST['insContactInformation'];

            $result1 = executePlainSQL("SELECT Name, StudentNumber, ContactInformation FROM Resident WHERE StudentNumber='" . $StudentNumber . "'");

            executePlainSQL("UPDATE Resident SET ContactInformation='" . $ContactInformation . "' WHERE StudentNumber='" . $StudentNumber . "'");

            // $result2 = executePlainSQL("SELECT Name, StudentNumber, ContactInformation FROM Resident");
            $result2 = executePlainSQL("SELECT Name, StudentNumber, ContactInformation FROM Resident WHERE StudentNumber='" . $StudentNumber . "'");

            echo "<table>";
            echo "<tr><th>Name</th><th>StudentNumber</th><th>Contact Information</th></tr>";

            echo "<tr><th>Before: </th></tr>";
            while ($row = OCI_Fetch_Array($result1, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
            }

            echo "<tr><th>After: </th></tr>";
            while ($row = OCI_Fetch_Array($result2, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
            }

            echo "</table>";


            OCICommit($db_conn);
        }

        function devModeTable() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM Resident");

          echo "<tr><th>Student Number</th><th>Name</th><th>ContactInformation</th><th>PreferenceList</th><th>Age</th><th>University</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td><td>" . $row[5] . "</td></tr>";
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
                if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTESs
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {

                disconnectFromDB();
            }
        }

		if (isset($_POST['updateSubmit'])) {
            handlePOSTRequest();
        }
		?>

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
