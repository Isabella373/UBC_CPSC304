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
  <?php
    // Start the session
    session_start();
    ?>
  <html>
    <head>
        <title>Job Portal</title>
    </head>

    <body>
        <h2>Find User(s)</h2>
        <form method="GET" action="find_user.php">
            <input type="hidden" id="findUserQueryRequest" name="findUserQueryRequest">
            1st Attribute:
            <select name="attribute1" id="attribute1">
                <option value="None" name="None">None</option>
                <option value="Name" name="Name">Name</option>
                <option value="EmailAddress" name="EmailAddress">Email Address</option>
                <option value="PhoneNumber" name="PhoneNumber">Phone Number</option>
            </select>
            = <input type="text" name="val1" disabled>
            <br><br>

            <div id="second" style="display: none;">
                2nd Attribute:
                <select name="attribute2" id="attribute2">
                </select>
                = 
                <input type="text" name="val2" disabled>
                <br><br>
            </div>

            <div id="third" style="display: none;">
                3rd Attribute:
                <select name="attribute3" id="attribute3">
                </select>
                = 
                <input type="text" name="val3" disabled>
                <br><br>
            </div>
            <input type="submit" value="Find User(s)" name="findSubmit">
        </form>
        <a href="job_portal.php"><button>Go Back to Main Page</button></a>
        <br><br>

        <script>
            attribute1.addEventListener('change', function () {
                var val1 = document.getElementsByName('val1')[0];
                var val2 = document.getElementsByName('val2')[0];
                var options = [];

                third.style.display = 'none';
                val2.required = false;
                val2.disabled = true;

                if (attribute1.value == 'Name') {
                    second.style.display = 'block';
                    val1.required = true;
                    val1.disabled = false;
                    options = ["None", "OR EmailAddress", "AND EmailAddress", "OR PhoneNumber", "AND PhoneNumber"];
                } else if (attribute1.value == 'EmailAddress') {
                    second.style.display = 'block';
                    val1.required = true;
                    val1.disabled = false;
                    options = ["None", "OR Name", "AND Name", "OR PhoneNumber", "AND PhoneNumber"];
                } else if (attribute1.value == 'PhoneNumber') {
                    second.style.display = 'block';
                    val1.required = true;
                    val1.disabled = false;
                    options = ["None", "OR Name", "AND Name", "OR EmailAddress", "AND EmailAddress"];
                } else {
                    second.style.display = 'none';
                    val1.required = false;
                    val1.disabled = true;
                }

                while (attribute2.options.length > 0) {
                    attribute2.remove(0);
                }

                for (var i = 0; i < options.length; i++) {
                    var option = document.createElement("option");
                    option.value = options[i];
                    option.text = options[i];
                    attribute2.appendChild(option);
                }
            });


            attribute2.addEventListener('change', function () {
                var val2 = document.getElementsByName('val2')[0];
                var val3 = document.getElementsByName('val3')[0];
                var options = [];
                
                val3.required = false;
                val3.disabled = true;
                
                if (attribute2.value === 'AND Name' || attribute2.value === 'OR Name') {
                    third.style.display = 'block';
                    val2.required = true;
                    val2.disabled = false;
                    if (attribute1.value == 'EmailAddress') {
                        options = ["None", "OR PhoneNumber", "AND PhoneNumber"];
                    } else {
                        options = ["None", "OR EmailAddress", "AND EmailAddress"];
                    }
                } else if (attribute2.value === 'AND EmailAddress' || attribute2.value === 'OR EmailAddress') {
                    third.style.display = 'block';
                    val2.required = true;
                    val2.disabled = false;
                    if (attribute1.value == 'Name') {
                        options = ["None", "OR PhoneNumber", "AND PhoneNumber"];
                    } else {
                        options = ["None", "OR Name", "AND Name"];
                    }
                } else if (attribute2.value === 'AND PhoneNumber' || attribute2.value === 'OR PhoneNumber') {
                    third.style.display = 'block';
                    val2.required = true;
                    val2.disabled = false;
                    if (attribute1.value == 'Name') {
                        options = ["None", "OR EmailAddress", "AND EmailAddress"];
                    } else {
                        options = ["None", "OR Name", "AND Name"];
                    }
                } else {
                    third.style.display = 'none';
                    val2.required = false;
                    val2.disabled = true;
                }

                while (attribute3.options.length > 0) {
                    attribute3.remove(0);
                }

                for (var i = 0; i < options.length; i++) {
                    var option = document.createElement("option");
                    option.value = options[i];
                    option.text = options[i];
                    attribute3.appendChild(option);
                }
            });

            attribute3.addEventListener('change', function () {
                var val3 = document.getElementsByName('val3')[0];
                if (attribute3.value == 'None') {
                    val3.required = false;
                    val3.disabled = true;
                } else {
                    val3.required = true;
                    val3.disabled = false;
                }
            });
        </script>

        <?php
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

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_xli2801", "a80002512", "dbhost.students.cs.ubc.ca:1522/stu");

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

        function handlefindRequest() {
            global $db_conn;
        
            $attribute1 = $_GET['attribute1'];
            $val1 = htmlspecialchars($_GET['val1']);
            $attribute2 = $_GET['attribute2'];
            $val2 = htmlspecialchars($_GET['val2']);
            $attribute3 = $_GET['attribute3'];
            $val3 = htmlspecialchars($_GET['val3']);
        
            $query = "SELECT * FROM Users";
        
            if ($attribute1 != 'None' && !empty($val1)) {
                $query .= " WHERE $attribute1 = '$val1'";
            }
        
            if ($attribute2 != 'None' && !empty($val2)) {
                $query .= " $attribute2 = '$val2'";
            }
        
            if ($attribute3 != 'None' && !empty($val3)) {
                $query .= " $attribute3 = '$val3'";
            }
        
            $result = executePlainSQL($query);
        
            echo "<table border='1'><tr>";
            echo "<th>UserName</th>";
                echo "<th>Name</th>";
                echo "<th>Email Address</th>";
                echo "<th>Phone Number</th>";
                echo "<th>Description</th>";
                echo "</tr><tr>";

            $rowsFetched = false;
        
            while ($row = oci_fetch_array($result, OCI_ASSOC)) {
                $rowsFetched = true;
                foreach ($row as $column) {
                    echo "<td>$column</td>";
                }
                echo "</tr>";
            }
        
            echo "</table>";

            if (!$rowsFetched) {
                echo "<p style='color: blue;'>No tuple found.</p>";
            }
        }

		if (isset($_GET['findSubmit'])) {
            if (connectToDB()) {
                if (array_key_exists('findUserQueryRequest', $_GET)) {
                    handlefindRequest();
                }

                disconnectFromDB();
            }
        }
		?>
	</body>
</html>