<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values

  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your ORACLE username and password -->

<!-- <html style="background-color:#FFFF00;"> -->
<html>
    <head>
        <title>University Housing and Food Services</title>
    </head>

    <body>

        <h1>University Housing and Food Services</h1>

        <hr />

        <form method="POST" action="project.php">
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

        <hr />

        <a href="pages/insert.php"><h2>Hire New Cleaning Staff</h2></a>

        <hr />

        <a href="pages/insertRE.php"><h2>Add Resident (Does Not Process Room Assignment)</h2></a>

        <hr />

        <a href="pages/assign.php"><h2>Assign Resident to Room</h2></a>

        <hr />

        <a href="pages/delete.php"><h2>Delete a Resident</h2></a>

        <hr />

        <a href="pages/update.php"><h2>Update Resident Contact Information</h2></a>

        <hr />

        <a href="pages/select.php"><h2>Select Staff Paid Above Given Wage</h2></a>

        <hr />

        <a href="pages/projection.php"><h2>Find All Student Numbers of Residents and RAs Older Than the Specified Age</h2></a>

        <hr />

        <a href="pages/join.php"><h2>Find All Student Numbers & Corresponding Room Numbers of Residents and RAs Assigned to the Given Room Type</h2></a>

        <hr />

        <a href="pages/group.php"><h2>Find the Number of Cleaning Staff or the Lowest/Highest/Average Wage of All Cleaning Staff for Each Building</h2></a>

        <hr />

        <a href="pages/having.php"><h2>Find Room Types Whose Residents' Average Age is Greater Than the Specified Age</h2></a>

        <hr />

        <a href="pages/nested.php"><h2>Find Distinct Wages Below the Average Wage of Employees Who Earn Above the Overall Average Wage Among All Staff That Are Earned By More Than One Employee</h2></a>

        <hr />

        <a href="pages/division.php"><h2>Find All Cleaning Staff Who have Worked In Every Building</h2></a>

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

        function handleResetRequest() {
            global $db_conn;

            $sql = file_get_contents('setup.sql');

            while(true) {
              $pos = strpos($sql, ";");
              if (!$pos) {
                break;
              }
              executePlainSQL(substr($sql , 0, $pos));
              $sql = substr($sql , $pos + 1);
            }

            echo "<script type='text/javascript'>alert('Database has been reset!');</script>";
        }

        function test() {
          connectToDB();

          $result = executePlainSQL("SELECT Building_Name FROM Building");


          disconnectFromDB();
          return $result;
        }

        function printResultSelect($result, $Wage) {
            echo "<br>Staff Paid Above $$Wage per hour:<br>";
            echo "<table>";
            echo "<tr><th>SIN</th><th>Name</th><th>Wages</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
            }

            echo "</table>";
        }

        function printResultProjection($result, $Age) { //s results from a select statement
            echo "<br>All Residents and RAs older than $Age:<br>";
            echo "<table>";
            echo "<tr><th>Student Number</th><th>Name</th><th>Age</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>";
            }

            echo "</table>";
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

        function printResultHaving($result, $age) {
          echo "<br>Find Room Types Whose Residents' Average Age is Greater Than $age<br>";
          echo "<table>";
          echo "<tr><th>Room Type</th><th>Average Age</th></tr>";

          while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
              echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
          }

          echo "</table>";
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

        function handleUpdateRequest() {
            global $db_conn;

            $StudentNumber =  $_POST['insStudentNumber'];
            $ContactInformation = $_POST['insContactInformation'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE Resident SET ContactInformation='" . $ContactInformation . "' WHERE StudentNumber='" . $StudentNumber . "'");
            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['insSINum'],
                ":bind2" => $_POST['insWages'],
                ":bind3" => $_POST['insName'],
                ":bind4" => $_POST['insContactInformation'],
                ":bind5" => $_POST['insUniversity']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into CleaningStaff values (:bind1, :bind2, :bind3, :bind4, :bind5)", $alltuples);
            OCICommit($db_conn);
        }

        function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM demoTable");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
            }
        }

        function handleDeleteRequest() {
            global $db_conn;

            $StudentNumber =  $_POST['insStudentNumber'];

            executePlainSQL("DELETE FROM Resident WHERE StudentNumber='" . $StudentNumber . "'");
            executePlainSQL("DELETE FROM LivesInRE WHERE StudentNumberRE='" . $StudentNumber . "'");
            executePlainSQL("DELETE FROM AssignRE WHERE StudentNumberRE='" . $StudentNumber . "'");

            OCICommit($db_conn);
        }

        function handleSelectRequest() {
            global $db_conn;

            $Wage =  $_GET['insWage'];

            $result = executePlainSQL(" SELECT SINum, Name, Wages
                                        FROM CleaningStaff
                                        WHERE Wages >'" . $Wage . "'
                                        UNION
                                        SELECT SINum, Name, Wages
                                        FROM DiningHallStaff
                                        WHERE Wages >'" . $Wage . "'
                                        UNION
                                        SELECT SINum, Name, Wages
                                        FROM FrontDeskStaff
                                        WHERE Wages >'" . $Wage . "'");
            printResultSelect($result, $Wage);
        }

        function handleProjectionRequest() {
            global $db_conn;

            $Age =  $_GET['insAge'];

            $result = executePlainSQL(" SELECT StudentNumber, Name, Age
                                        FROM Resident
                                        WHERE Age >'" . $Age . "'
                                        UNION
                                        SELECT StudentNumber, Name, Age
                                        FROM ResidenceAdvisor
                                        WHERE Age >'" . $Age . "'");

            printResultProjection($result, $Age);
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

        function handleHavingRequest() {
            global $db_conn;

            $age =  $_GET['insAge'];

            $result = executePlainSQL("SELECT r.RoomType, AVG(re.Age)
                                       FROM Room r, AssignRE are, Resident re
                                       WHERE r.RoomNumber = are.RoomNumber AND
                                             r.Address = are.Address  AND
                                             r.Name = are.Name AND
                                             re.StudentNumber = are.StudentNumberRE
                                       GROUP BY r.RoomType
                                       HAVING AVG(re.Age) > $age");
            printResultHaving($result, $age);
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


            $result = executePlainSQL(" SELECT Wages, SINum
                                        FROM tempWages
                                        WHERE Wages = (SELECT Max(Wages)
                                                       FROM ( SELECT Wages, SINum
                                                              FROM  tempWages
                                                              WHERE Wages < ( SELECT AVG(Wages)
                                                                              FROM  ( SELECT Wages, SINum
                                                                                      FROM  tempWages
                                                                                      WHERE Wages > ( SELECT AVG(Wages)
                                                                                                      FROM  tempWages)))))");
             echo "<table>";
             echo "<tr><th>Avg</th></tr>";

             while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                 echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
             }

             echo "</table>";
        }

        function handleDivisionRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT cs.SINum, cs.Name, cs.ContactInformation
                                       FROM CleaningStaff cs
                                       WHERE NOT EXISTS(
                                          SELECT b.Building_Address, b.Building_Name
                                          FROM Building b
                                          MINUS
                                          SELECT Address AS Building_Address, Name AS Building_Name
                                          FROM Cleans c
                                          WHERE cs.SINum = c.SINum)");
            echo "<table>";
            echo "<tr><th>Avg</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
            }

            echo "</table>";

        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                } else if (array_key_exists('deleteQueryRequest', $_POST)) {
                    handleDeleteRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countTuples', $_GET)) {
                    handleCountRequest();

                } else if (array_key_exists('selectSubmit', $_GET)) {
                    handleSelectRequest();

                } else if (array_key_exists('projectionSubmit', $_GET)) {
                    handleProjectionRequest();

                } else if (array_key_exists('joinSubmit', $_GET)) {
                    handleJoinRequest();

                } else if (array_key_exists('groupSubmit', $_GET)) {
                    handleGroupRequest();

                } else if (array_key_exists('havingSubmit', $_GET)) {
                    handleHavingRequest();

                } else if (array_key_exists('nestedSubmit', $_GET)) {
                    handleNestedRequest();

                } else if (array_key_exists('divisionSubmit', $_GET)) {
                    handleDivisionRequest();

                }
                disconnectFromDB();
            }
        }

		if (  isset($_POST['reset']) ||
          isset($_POST['updateSubmit']) ||
          isset($_POST['insertSubmit']) ||
          isset($_POST['deleteSubmit'])
        ) {
            handlePOSTRequest();
        } else if ( isset($_GET['countTupleRequest']) ||
                    isset($_GET['selectQueryRequest']) ||
                    isset($_GET['projectionQueryRequest']) ||
                    isset($_GET['joinQueryRequest']) ||
                    isset($_GET['groupQueryRequest']) ||
                    isset($_GET['havingQueryRequest']) ||
                    isset($_GET['nestedQueryRequest']) ||
                    isset($_GET['divisionQueryRequest'])
                  ) {
            handleGETRequest();
        }
		?>
	</body>
</html>
