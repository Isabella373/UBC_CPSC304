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
        <h2>Reset</h2>
        <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

        <form method="POST" action="job_portal.php">
            <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

        <?php
        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function handleResetRequest() {
            global $db_conn;
            global $success;

            // username and password for demo
            // john_doe             johnpassword123
            // jane_smith           janepassword456!
            // michael_johnson      michaelpassword789
            // emily_brown          emilypassword123
            // william_davis        williampassword456
            // olivia_wilson        oliviapassword789
            // james_miller         jamespassword123
            // ava_jones            avapassword456
            // robert_lee           robertpassword789
            // sohpia_taylor        sophiapassword123

            $sqlContent = file_get_contents('initialize.sql'); // import initialize.sql
            $sqlQueries = explode(';', $sqlContent);
            
            foreach ($sqlQueries as $sqlQuery) {
                executePlainSQL($sqlQuery);
                OCICommit($db_conn);
            }
            if ($success == True) {
                echo ("<p style='color: blue;'>Successfully resetted.</p>");
            }
        }

		if (isset($_POST['reset'])) {
            handlePOSTRequest();
        }
        ?>

        <hr />

        <h2>View All Table</h2>
        <a href="job_portal_view.php"><button>View All Table</button></a>

        <hr />

        <h2>Find User(s)</h2>
        <a href="find_user.php"><button>Find User(s)</button></a>
        <hr />


        <h2>User Sign-up</h2>
        <form method="POST" action="job_portal.php">
            <input type="hidden" id="insertUserQueryRequest" name="insertUserQueryRequest">
            
            Username* <input type="text" name="username" required="required"> <br><br>
            Password* <input type="password" name="password" required="required"> <br><br>

            <label for="userTypeSelect">User Type* </label>
            <select name="userType" id="userTypeSelect" required="required">
                <option disabled selected value> -- select an option -- </option>
                <option value="recruiter" name="recruiter">Recruiter</option>
                <option value="jobseeker" name="jobseeker">Job Seeker</option>
            </select>
            <br><br>
            
            Name*  <input type="text" name="name" required="required"> <br><br>
            Email Address* <input type="email" name="email" required="required"> <br><br>
            Phone Number (Eg. 123-456-7890) <input type="text" name="phone"> <br><br>
            Description <input type="text" name="description"> <br><br>

            <div id="companyInfo" style="display: none;">
                <label for="companyOption">Company* </label>
                <select name="companyOption" id="companyOption">
                    <option disabled selected value> -- select an option -- </option>
                    <option value="existing" name="existing">Use Existing Company ID</option>
                    <option value="createNew" name="createNew">Create New Company</option>
                </select>
                <br><br>
                <div id="existingCompany" style="display: none;">
                    Company ID* <input type="text" name="companyID"> <br><br>
                </div>
                <div id="newCompany" style="display: none;">
                    New Company Info:<br />
                    Company Name* <input type="text" name="companyName"> <br><br>
                    Company Address <input type="text" name="companyAddress"> <br><br>
                </div>
            </div>

            <input type="submit" value="Sign Up" name="insertSubmit">
        </form>

        <script>
            const userTypeSelect = document.getElementById('userTypeSelect');
            const companyInfoDiv = document.getElementById('companyInfo');
            const existingCompanyDiv = document.getElementById('existingCompany');
            const newCompanyDiv = document.getElementById('newCompany');
            const companyOption = document.getElementById('companyOption');

            userTypeSelect.addEventListener('change', function () {
                if (userTypeSelect.value === 'recruiter') {
                    companyInfoDiv.style.display = 'block';
                    companyOption.required = true;
                } else {
                    companyInfoDiv.style.display = 'none';
                    companyOption.required = false;
                }
            });

            companyOption.addEventListener('change', function () {
                if (companyOption.value === 'existing') {
                    existingCompanyDiv.style.display = 'block';
                    newCompanyDiv.style.display = 'none';
                    document.getElementById('companyID').required = true;
                    document.getElementById('companyName').required = false;
                } else if (companyOption.value === 'createNew') {
                    existingCompanyDiv.style.display = 'none';
                    newCompanyDiv.style.display = 'block';
                    document.getElementById('companyID').required = false;
                    document.getElementById('companyName').required = true;
                }
            });
        </script>

        <?php
        function handleInsertUserRequest() {
            global $db_conn, $success;

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])) {
                echo "<p style='color: red;'>Invalid username, only alphanumeric characters and underscores are allowed, please try again</p>";
                return;
            }
            if (!preg_match('/^[a-zA-Z\s]+$/', $_POST['name'])) {
                echo "<p style='color: red;'>Invalid format for name, please try again.</p>";
                return;
            }
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                echo "<p style='color: red;'>Invalid email, please try again.</p>";
                return;
            }
            if (!empty($_POST['phone']) && !preg_match('/^\d{3}-\d{3}-\d{4}$/', $_POST['phone'])) {
                echo "<p style='color: red;'>Invalid format for phone number, please try again.</p>";
                return;
            }

            //login info insert
            $logintuple = array (
                ":bind1" => htmlspecialchars($_POST['username']),

                ":bind2" => password_hash($_POST['password'], PASSWORD_DEFAULT)
            );

            $loginAlltuples = array ($logintuple);
            executeBoundSQL("insert into UserLogInfo values (:bind1, :bind2)", $loginAlltuples);
            OCICommit($db_conn);

            if (!$success) {
                echo ("<p style='color: red;'>Sign up failed: Username already exists.</p>");
                return;
            }
            
            //user insert
            $userTuple = array(
                ":bind1" => htmlspecialchars($_POST['username']),
                ":bind2" => htmlspecialchars($_POST['name']),
                ":bind3" => htmlspecialchars($_POST['email']),
                ":bind4" => htmlspecialchars($_POST['phone']),
                ":bind5" => htmlspecialchars($_POST['description']  , ENT_QUOTES, 'UTF-8')
            );

            $userAlltuples = array($userTuple);
            executeBoundSQL("insert into Users values (:bind1, :bind2, :bind3, :bind4, :bind5)", $userAlltuples);
            OCICommit($db_conn);

            if (!$success) {
                echo ("<p style='color: red;'>Sign up failed: Email already exists.</p>");
                executeBoundSQL("delete from UserLogInfo where UserName = (:bind1)", $loginAlltuples);
                OCICommit($db_conn);
                return;
            }

            if ($_POST['userType'] == "jobseeker") {
                executeBoundSQL("insert into JobSeekers values (:bind1)", $userAlltuples);
                OCICommit($db_conn);
                echo ("<p style='color: green;'>Successfully signed up.</p>");
            } else {
                if ($_POST['companyOption'] == "createNew") {
                    $companyTuple = array(
                        ":bind1" => htmlspecialchars($_POST['companyName']),
                        ":bind2" => htmlspecialchars($_POST['companyAddress'])
                    );
        
                    $companyAlltuples = array($companyTuple);
                    executeBoundSQL("insert into Companies values (CompanyId_Sequence.nextval, :bind1, :bind2)", $companyAlltuples);
                    OCICommit($db_conn);
                    $companyId = executePlainSQL("SELECT CompanyId_Sequence.currval FROM dual");
                    $id = oci_fetch_assoc($companyId)['CURRVAL'];
                    echo "<br> The company id is: " . $id . "<br>";
                }
                if ($success) {
                    if ($_POST['companyOption'] == "existing") {
                        $id = $_POST['companyID'];
                    }
                    //user insert
                    $recruiterTuple = array(
                        ":bind1" => $_POST['username'],
                        ":bind2" => $id
                    );
        
                    $recruiterAlltuples = array($recruiterTuple);
                    executeBoundSQL("insert into Recruiters values (:bind1, :bind2)", $recruiterAlltuples);
                    OCICommit($db_conn);

                    if ($success == FALSE) {
                        echo ("<p style='color: red;'>Sign up failed: Invalid company ID.</p>");
                        executeBoundSQL("delete from UserLogInfo where UserName = (:bind1)", $loginAlltuples);
                        executeBoundSQL("delete from Users where UserName = (:bind1)", $userAlltuples);
                        executeBoundSQL("delete from Companies where CompanyId = (:bind2)", $recruiterAlltuples);
                        OCICommit($db_conn);
                    } else {
                        echo ("<p style='color: green;'>Successfully signed up.</p>");
                    }
                } else {
                    echo ("<p style='color: red;'>Sign up failed: Company already exists</p>");
                    executeBoundSQL("delete from UserLogInfo where UserName = (:bind1)", $loginAlltuples);
                    executeBoundSQL("delete from Users where UserName = (:bind1)", $userAlltuples);
                    OCICommit($db_conn);
                }
            }
        }

        if (isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        }
        ?>

        <hr />

        <h2>User Log-in</h2>
        <form method="POST" action="login_validate.php">
            <input type="hidden" id="loginQueryRequest" name="loginQueryRequest">
            
            Username: <input type="text" name="username" required="required"> <br><br>
            Password: <input type="password" name="password" required="required"> <br><br>

            <input type="submit" value="Log In" name="loginSubmit">
        </form>

        <?php
        echo "<p style='color: red;'>" . $_SESSION["error_message"] . "</p>";
        session_unset(); 
        ?>

        <hr />

        <h2>Count Users</h2>
        <form method="GET" action="job_portal.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countUserLogInfoTupleRequest" name="countUserLogInfoTupleRequest">
            <input type="submit" name="countTuples1"></p>
        </form>

        <h2>Count Recruiters</h2>
        <form method="GET" action="job_portal.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countRecruitersTupleRequest" name="countRecruitersTupleRequest">
            <input type="submit" name="countTuples2"></p>
        </form>

        <h2>Count Job Seekers</h2>
        <form method="GET" action="job_portal.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countJobSeekersTupleRequest" name="countJobSeekersTupleRequest">
            <input type="submit" name="countTuples3"></p>
        </form>

        <h2>Count Companies</h2>
        <form method="GET" action="job_portal.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countCompaniesTupleRequest" name="countCompaniesTupleRequest">
            <input type="submit" name="countTuples4"></p>
        </form>


        <?php
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

        function handleCountRequest1() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM UserLogInfo");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in UserLogInfo: " . $row[0] . "<br>";
            }
        }
        function handleCountRequest2() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM Companies");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in Companies: " . $row[0] . "<br>";
            }
        }
        function handleCountRequest3() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM Recruiters");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in Recruiters: " . $row[0] . "<br>";
            }
        }

        function handleCountRequest4() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM JobSeekers");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in Job Seeker: " . $row[0] . "<br>";
            }
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('insertUserQueryRequest', $_POST)) {
                    handleInsertUserRequest();
                }  else if (array_key_exists('loginQueryRequest', $_POST)) {
                    handleLoginRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countUserLogInfoTupleRequest', $_GET)) {
                    handleCountRequest1();
                } else if (array_key_exists('countCompaniesTupleRequest', $_GET)) {
                    handleCountRequest2();
                } else if (array_key_exists('countRecruitersTupleRequest', $_GET)) {
                    handleCountRequest3();
                } else if (array_key_exists('countJobSeekersTupleRequest', $_GET)) {
                    handleCountRequest4();
                }

                disconnectFromDB();
            }
        }

		if (isset($_POST['loginSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTuples1']) || isset($_GET['countTuples2']) || isset($_GET['countTuples3']) || isset($_GET['countTuples4'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>