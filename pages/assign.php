<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

      <h2>Assign Unassigned Resident to Room</h2>

      Shared Rooms have a capacity of 4. Double Rooms have a capacity of 2. All RAs are assumed to be assigned to Single and Studio rooms. <br />
      All residents (RAs not included) without assignment and rooms with space are displayed in the drop down lists below.


      <hr />

      <form method="POST" action="assign.php"> <!--refresh page when submitted-->
          <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
          Student Number: <select id="StudentNumber" name="insStudentNumber">
              <?php
                $result = test();
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                  $name = $row[0];
                  echo "<option value='$name'>$name</option>";
                }
              ?>
          </select> <br /><br />

          Room: <select id="Room" name="insRoom">
              <?php
                $result = test1();
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                  $name = $row[0] . "|" . $row[1] . "|". $row[2];
                  echo "<option value='$name'>$name</option>";
                }
              ?>
          </select> <br /><br />

          <input type="submit" value="Submit" name="insertSubmit"></p>
      </form>


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

          $result = executePlainSQL(" SELECT StudentNumber
                                      FROM Resident r
                                      WHERE NOT EXISTS (
                                        SELECT StudentNumberRE
                                        FROM AssignRE
                                        WHERE StudentNumberRE = r.StudentNumber
                                      )");

          disconnectFromDB();

          return $result;
        }

        function test1() {
          connectToDB();

          $result = executePlainSQL(" SELECT r.Name, r.Address, r.RoomNumber
                                      FROM Room r
                                      WHERE NOT EXISTS (
                                        SELECT are.Name, are.Address, are.RoomNumber
                                        FROM AssignRE are
                                        WHERE (are.Address = r.Address AND
                                              are.Name = r.Name AND
                                              are.RoomNumber = r.RoomNumber)
                                      )
                                      OR (  r.RoomType = 'Shared' AND
                                            4 > ( SELECT COUNT(*)
                                                  FROM AssignRE aree
                                                  WHERE (aree.Address = r.Address AND
                                                        aree.Name = r.Name AND
                                                        aree.RoomNumber = r.RoomNumber)))
                                      OR (  r.RoomType = 'Double' AND
                                            2 > ( SELECT COUNT(*)
                                                  FROM AssignRE aree
                                                  WHERE (aree.Address = r.Address AND
                                                        aree.Name = r.Name AND
                                                        aree.RoomNumber = r.RoomNumber)))
                                      MINUS
                                      SELECT r.Name, r.Address, r.RoomNumber
                                      FROM Room r
                                      WHERE EXISTS (
                                        SELECT are.Name, are.Address, are.RoomNumber
                                        FROM AssignRA are
                                        WHERE are.Address = r.Address AND
                                              are.Name = r.Name AND
                                              are.RoomNumber = r.RoomNumber
                                      )");

          // while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
          //     echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
          // }
          //
          disconnectFromDB();

          return $result;
        }

        function handleInsertRequest() {
            global $db_conn;

             $sn = $_POST['insStudentNumber'];
             $room = $_POST['insRoom'];

             $name = substr($room , 0, strpos($room, "|"));
             $room = substr($room , strpos($room, "|") + 1);
             $addr = substr($room , 0, strpos($room, "|"));
             $room = substr($room , strpos($room, "|") + 1);
             $roomNumber = substr($room , 0);

             $tuple = array (
                 ":bind1" => $addr,
                 ":bind2" => $roomNumber,
                 ":bind3" => $name,
                 ":bind4" => $sn
             );

             $alltuples = array (
                 $tuple
             );

            executeBoundSQL("insert into AssignRE values (:bind1, :bind2, :bind3, :bind4)", $alltuples);

            $result = executePlainSQL(" SELECT r.Residence_Name
                                        FROM Residence r
                                        WHERE EXISTS (
                                          SELECT *
                                          FROM Grouped g
                                          WHERE g.Building_Address = '" . $addr . "' AND
                                                g.Building_Name = '" . $name . "' AND
                                                r.Residence_Name = g.Residence_Name
                                        )");

            $resName = OCI_Fetch_Array($result, OCI_BOTH)[0];

            $tuple = array (
                ":bind1" => $resName,
                ":bind2" => $sn,
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into LivesInRE values (:bind1, :bind2)", $alltuples);

            OCICommit($db_conn);

            header("Refresh:0");
        }

        function devModeTable1() {

          connectToDB();

          $result = executePlainSQL("SELECT * FROM LivesInRE");

          echo "<tr><th>LivesInRE</th></tr>";

          echo "<tr><th>Residence Name</th><th>Student Number</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
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
                if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {

                disconnectFromDB();
            }
        }

		if (isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        }
		?>

    <hr />

    <form action="../project.php">
      <input type="submit" value="Back" />
    </form>

    <hr />

    <form>
    <input type="button" id='devButton' value="Developer Mode ON" name="DEVMODE"></p>
    
    <table id='devTable1' style="display:initial;" style="float:left;">
      <?php devModeTable1();?>
    </table>

    <table id='devTable2' style="display:initial;" style="float:left;">
      <?php devModeTable2();?>
    </table>

    <script>
      let button = document.getElementById('devButton');
      let table1 = document.getElementById('devTable1');
      let table2 = document.getElementById('devTable2');

      button.addEventListener('click', buttonfn);

      function buttonfn() {
        if (button.value === 'Developer Mode OFF') {
          button.value = 'Developer Mode ON';
          table1.style.display = 'initial';
          table2.style.display = 'initial';
        } else {
          button.value = 'Developer Mode OFF';
          table1.style.display = 'none';
          table2.style.display = 'none';
        }
      }
    </script>
    </form>

	</body>
</html>
