
<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up All OCI commands are
  commands to the Oracle libraries. To get the file to work, you must place it
  somewhere where your Apache server can run it, and you must rename it to have
  a ".php" extension. You must also change the username and password on the
  oci_connect below to be your ORACLE username and password
-->

<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_xli2801";			// change "cwl" to your own CWL
$config["dbpassword"] = "a80002512";	// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>


<?php
session_start();

// Check if the username is provided in the URL parameters
if (isset($_GET['username']) && $_GET['username']!='') {

    $_SESSION['username'] = $_GET['username'];
}
// Check if the username is set in the session
if (isset($_SESSION['username'])) {
    // Display the username
    echo "Hi! {$_SESSION['username']}";
} else {
    echo "<p style='color: red;'>Invalid Access</p>";
    header("Location: job_portal.php");
    exit();
}
?>


<html>

<head>
    <title>CPSC 304 PHP/Oracle Demonstration</title>
</head>

<body>
<h2>LogOut</h2>
<p>You can log out to reset the table</p>

<form method="POST" action="logout.php">
    <!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
    <input type="hidden" id="logOutRequest" name="logOutRequest">
    <p><input type="submit" value="Log Out" name="LogOut"></p>
</form>


<hr>
<h2>Jobseeker View All Incomplete Applications</h2>
<form method="GET" action="job_seeker.php">
    <input type="hidden" id="displayIncompleteApplicationsRequest" name="displayIncompleteApplicationsRequest">
    <input type="submit" value="View Incomplete Applications" name="displayIncompleteApplications"></p>
</form>


<?php

// all functions for enabling jobseeker to view/delete incomplete applications
function handleDisplayIncompleteApplicationsRequest() {
    global $db_conn;
    $jobSeekerUserName= $_SESSION["username"];
    $result = executePlainSQL(
        "SELECT
				Applications.ApplicationId,
				Applications.CreateDate,
				Applications.Resume,
				Applications.CoverLetter

			FROM
				JobSeekers,
				Resumes,
				Applications
			WHERE
				Resumes.JobSeekerId = JobSeekers.UserName
				AND JobSeekers.UserName = '$jobSeekerUserName'
				AND Applications.Resume = Resumes.Resume
				AND Applications.Status = 'Incomplete application'

				");
    oci_commit($db_conn);

    $rowsFetched = false; // used to track if have result

    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        $rowsFetched = true;

        echo "<div style='border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;'>
						<p><strong>ApplicationId:</strong> {$row["APPLICATIONID"]}</p>
						<p><strong>Create Date:</strong> {$row["CREATEDATE"]}</p>
						<p><strong>Resume:</strong>{$row["RESUME"]}</p>
						<p><strong>Cover Letter:</strong> {$row["COVERLETTER"]}</p>
		
						<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
							<input type='hidden' name='deleteIncompleteApplicationsRequest'>
							<button type='submit' name='deleteIncompleteApplications' value='{$row["APPLICATIONID"]}'>Delete</button>
						</form>
					</div>";
    }

    if (!$rowsFetched) {
        echo "<p style='color: blue;'>No incomplete applications found.</p>";
    }

    echo "</div>";
}


function handleDeleteIncompleteApplicationsRequest($applicationId){
    global $db_conn, $success;

    executePlainSQL("DELETE FROM Applications WHERE ApplicationId = {$applicationId}");

    if ($success){
        echo "<p style='color: green;'>Incomplete application was deleted successfully</p>";
    }

    oci_commit($db_conn);

}

// all routes checker for jobseekers viewing/deleting applications
if (isset($_POST['deleteIncompleteApplications'])) {
    handlePOSTRequest();
} else if (isset($_GET['displayIncompleteApplications'])){
    handleGETRequest();
}
?>



<hr>
<h2>Jobseeker View All Resumes</h2>
<form method="GET" action="job_seeker.php">
    <input type="hidden" id="displayResumesRequest" name="displayResumesRequest">
    <input type="submit" value="View All Resumes" name="displayResumes"></p>
</form>


<?php

// all functions for enabling jobseeker to view/delete incomplete applications
function handleDisplayResumesRequest() {
    global $db_conn;
    $jobSeekerUserName= $_SESSION["username"];
    $result = executePlainSQL(
        "SELECT
				Resumes.Resume

			FROM
				JobSeekers,
				Resumes
			WHERE
				Resumes.JobSeekerId = JobSeekers.UserName
				AND  JobSeekers.UserName = '$jobSeekerUserName'
				

				");
    oci_commit($db_conn);

    $rowsFetched = false; // used to track if have result

    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        $rowsFetched = true;

        echo "<div style='border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;'>
						<p><strong>Resume:</strong>{$row["RESUME"]}</p>
		
						<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
							<input type='hidden' name='deleteResumesRequest'>
							<button type='submit' name='deleteResumes' value='{$row["RESUME"]}'>Delete</button>
						</form>
					</div>";
    }

    if (!$rowsFetched) {
        echo "<p style='color: blue;'>No resume found.</p>";
    }

    echo "</div>";
}


function handleDeleteResumesRequest($resume){
    global $db_conn, $success;

    executePlainSQL("DELETE FROM RESUMES WHERE Resume = '$resume'");
    if (!$success){
        echo "<p style='color: red;'>Unable to delete it as it is used in your application</p>";
    } else{
        echo "<p style='color: green;'>Resume was deleted successfully</p>";

    }

    oci_commit($db_conn);

}

// all routes checker for jobseekers viewing/deleting resume
if (isset($_POST['deleteResumes'])) {
    handlePOSTRequest();
} else if (isset($_GET['displayResumes'])){
    handleGETRequest();
}
?>




<hr>
<h2>Jobseeker Create New Applications</h2>
<form method="POST" action="job_seeker.php">
    <input type="hidden" id="createDraftApplicationsRequest" name="createDraftApplicationsRequest">
    <label for='coverLetter'>Cover Letter (Link):</label>
    <input type='text' name='coverLetter' placeholder='Enter link'><br><br>
    <label for='resume'>Resume (Link)*:</label>
    <input type='text' name='resume' placeholder='Enter link' required><br><br>
    <input type="submit" value="Create Applications" name="createDraftApplications"></p>
</form>

<?php
// all functions for enabling jobseeker create new applications (before applying/submitting)
function handleCreateDraftApplicationsRequest($coverLetter, $resume) {
    global $db_conn, $success;

    $jobSeekerUserName = $_SESSION["username"];

    // $jobSeekerUserName = $_GET["username"];
    $createDate = date('Y-m-d');

    // check if cover letter and resume are valid link
    if (filter_var($resume, FILTER_VALIDATE_URL) === FALSE) {
        echo "<p style='color: red;'>Invalid Resume URL, please try again!</p>";
        return;
    }
    if (!empty($coverLetter) && filter_var($coverLetter, FILTER_VALIDATE_URL) === FALSE) {
        echo "<p style='color: red;'>Invalid Cover Letter URL, please try again!</p>";
        return;
    }


    $result = executePlainSQL("SELECT Count(*) AS RESULT
											FROM Resumes
											WHERE Resumes.Resume = '$resume'
											AND Resumes.JobSeekerId = '$jobSeekerUserName'");

    $row = OCI_Fetch_Array($result, OCI_ASSOC);
    if ($row["RESULT"] == 0){ // this job seeker don't have that resume
        executePlainSQL(
            "INSERT INTO Resumes
						VALUES ('$resume', '$jobSeekerUserName')");
        oci_commit($db_conn);

        if ($success) {
            executePlainSQL(
                "INSERT INTO Applications
								VALUES (ApplicationId_Sequence.nextval, NULL, NULL, TO_DATE('$createDate','YYYY-MM-DD'), '$coverLetter', '$resume', 'Incomplete application', NULL)");
            if (!$success){
                executePlainSQL("DELETE FROM Resumes WHERE Resume='$resume'");
                oci_commit($db_conn);
            }
        }else{
            echo "<p style='color: red;'>Fail to submit Application due to duplicate Resume URL</p>";
            return;
        }

    } else{
        executePlainSQL(
            "INSERT INTO Applications
                    VALUES (ApplicationId_Sequence.nextval, NULL, NULL, TO_DATE('$createDate','YYYY-MM-DD'), '$coverLetter', '$resume', 'Incomplete application', NULL)");
        oci_commit($db_conn);
    }

    if ($success){
        echo "<p style='color: green;'>Application was submitted successfully</p>";
    } else{
        echo "<p style='color: red;'>Fail to submit Application</p>";
    }



}

// all routes checker for jobseekers creating draft applications
if (isset($_POST['createDraftApplications'])) {
    handlePOSTRequest();
}

?>



<hr>
<h2>Jobseeker View Job Posts</h2>
<form method="GET" action="job_seeker.php">
    <input type="hidden" id="displayJobPostsRequest" name="displayJobPostsRequest">
    <input type="submit" value="View Job Posts" name="displayJobPosts"></p>
</form>


<?php
// all functions for jobseekers viewing job posts and create/submit applications
function displayJobPosts($result){
    echo "<h2>Job Posts</h2>";
    echo "<div>";

    $rowsFetched = false; // used to track if have result
    $jobSeekerUserName = $_SESSION["username"];


    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        $rowsFetched = true;
        $isButtonDisabled = false;



        $checkIfApplied = executePlainSQL(
            "SELECT *
					FROM JobSeekers, Resumes, Applications, JobPosts
					WHERE JobSeekers.UserName = Resumes.JobSeekerId
					  AND Resumes.Resume = Applications.Resume
					  AND Applications.JobPostId = JobPosts.JobPostId
					  AND JobSeekers.UserName = '$jobSeekerUserName'
					  AND JobPosts.JobPostId = {$row["JOBPOSTID"]}"
        );

        $count = 0;
        while ($line = OCI_Fetch_Array($checkIfApplied, OCI_ASSOC)) {
            $count=$count+1;
        }

        if ($count != 0){
            $isButtonDisabled = true;
        }

        $jobLocation = $row["JOBLOCATION"] ?? '';
        $salary = $row["SALARY"] ?? '';
        $requirements = $row["REQUIREMENTS"] ?? '';

        echo "<div style='border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;'>
						<h3>{$row["JOBTITLE"]}</h3>
						<p><strong>Company:</strong> {$row["COMPANYNAME"]}</p>
						<p><strong>Location:</strong> {$jobLocation}</p>
						<p><strong>Job Type:</strong> {$row["JOBTYPE"]}</p>
						<p><strong>Salary:</strong> {$salary}</p>
						<p><strong>Post Date:</strong> {$row["POSTDATE"]}</p>
						<p><strong>Description:</strong> {$row["DESCRIPTION"]}</p>
						<p><strong>Requirements:</strong> {$requirements}</p>
						<p><strong>Deadline:</strong> {$row["DEADLINE"]}</p>
						<p><strong>Num of Applications:</strong> {$row["NUMOFAPPLICATIONS"]}</p>
						<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
						<input type='hidden' id='applyJobPostsRequest' name='applyJobPostsRequest'>
						<button type='submit' name='applyJobPosts' value='{$row["JOBPOSTID"]}' " . ($isButtonDisabled ? 'disabled' : '') . ">
						" . ($isButtonDisabled ? 'Applied' : 'Apply') . "
						</button>
					</form>
					</div>";
    }

    echo "</div>";

    if (!$rowsFetched) {
        echo "<p style='color: blue;'>No job posts found.</p>";
    }



}

function handleDisplayJobPostsRequest(){
    global $db_conn;
    $result = executePlainSQL(
        "SELECT
					JobPosts.JobPostId,
					JobPosts.Title As JobTitle,
					Companies.CompanyName,
					JobPosts.Location As JobLocation,
					JobPosts.JobType,
					JobPosts.Salary,
					JobPosts.PostDate,
					JobPosts.Description,
					JobPosts.Requirements,
					JobPosts.Deadline,
					JobPosts.NumOfApplications
				
				FROM
					JobPosts,
					Recruiters,
					Companies
				WHERE
					Recruiters.CompanyId = Companies.CompanyId
					AND JobPosts.RecruiterId = Recruiters.UserName
				");
    oci_commit($db_conn);
    displayJobPosts($result);
}

function handleApplyJobPostsRequest($jobPostId){
    global $db_conn;
    $result = executePlainSQL(
        "SELECT
					JobPosts.JobPostId,
					JobPosts.Title As JobTitle,
					Companies.CompanyName,
					JobPosts.Location As JobLocation,
					JobPosts.JobType,
					JobPosts.Salary,
					JobPosts.PostDate,
					JobPosts.Description,
					JobPosts.Requirements,
					JobPosts.Deadline,
					JobPosts.NumOfApplications
					
				FROM
					JobPosts,
					Recruiters,
					Companies
				WHERE
					Recruiters.CompanyId = Companies.CompanyId
					AND JobPosts.RecruiterId = Recruiters.UserName
					AND JobPosts.JobPostId = $jobPostId
			");
    $jobSeekerUserName = $_SESSION["username"];


    $allIncompleteApplications = executePlainSQL(
        "SELECT
					JobSeekers.UserName,
					Applications.ApplicationId,
					Applications.CreateDate,
					Applications.Resume,
					Applications.CoverLetter
		
				FROM
					JobSeekers,
					Resumes,
					Applications
				WHERE
					Resumes.JobSeekerId = JobSeekers.UserName
					AND JobSeekers.UserName = '$jobSeekerUserName'
					AND Applications.Resume = Resumes.Resume
					AND Applications.Status = 'Incomplete application'
			");
    oci_commit($db_conn);

    $incompleteApplications = [];
    while ($appRow = OCI_Fetch_Array($allIncompleteApplications, OCI_ASSOC)) {
        $incompleteApplications[$appRow['APPLICATIONID']] = $appRow['RESUME'];
    }

    $isButtonDisabled = false;
    if (count($incompleteApplications) == 0){
        $isButtonDisabled = true;
    }

    $row = OCI_Fetch_Array($result, OCI_ASSOC);

    $jobLocation = $row["JOBLOCATION"] ?? '';
    $salary = $row["SALARY"] ?? '';
    $requirements = $row["REQUIREMENTS"] ?? '';

    echo "<div style='border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;'>
				<h3>{$row["JOBTITLE"]}</h3>
				<p><strong>Company:</strong> {$row["COMPANYNAME"]}</p>
				<p><strong>Location:</strong> {$jobLocation}</p>
				<p><strong>Job Type:</strong>{$row["JOBTYPE"]}</p>
				<p><strong>Salary:</strong> {$salary}</p>
				<p><strong>Post Date:</strong> {$row["POSTDATE"]}</p>
				<p><strong>Description:</strong> {$row["DESCRIPTION"]}</p>
				<p><strong>Requirements:</strong> {$requirements}</p>
				<p><strong>Deadline:</strong> {$row["DEADLINE"]}</p>
				<p><strong>Num of Applications:</strong> {$row["NUMOFAPPLICATIONS"]}</p>
				<hr>
				<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
					<input type='hidden' name='applyWithRequest'>

					<button type='submit' name='applyWith' value='{$row["JOBPOSTID"]}' " . ($isButtonDisabled ? 'disabled' : '') . ">Apply With</button>
					<select name='selectedApplication'>";

    // Populate the dropdown
    foreach ($incompleteApplications as $appId => $resumeLink) {
        echo "<option value='$appId'>Application: $appId - $resumeLink</option>";
    }

    echo "</select><br>
				
				</form>
				<h4>OR</h4>
		
				<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
				<input type='hidden' name='submitApplicationRequest'>
				
				<label for='coverLetter'>Cover Letter (Link):</label>
				<input type='text' name='coverLetter' placeholder='Enter link'><br>
				
				<label for='resume'>Resume (Link)*:</label>
				<input type='text' name='resume' placeholder='Enter link' required><br>
		
				<button type='submit' name='submitApplication' value='{$row["JOBPOSTID"]}'>Submit</button>
			</form>
			</div>";

    echo "</div>";
}

function handleApplyWithRequest($jobPostId, $applicationId){
    global $db_conn, $success;



    $result = executePlainSQL(
        "SELECT
					JobPosts.JobPostId,
					JobPosts.RecruiterId
				FROM
					JobPosts
				WHERE
					JobPosts.JobPostId = $jobPostId
				");
    $currentDate = date('Y-m-d');
    $row = OCI_Fetch_Array($result, OCI_ASSOC);
    executePlainSQL(
        "UPDATE Applications
				SET Applications.RecruiterId = '{$row["RECRUITERID"]}',
					Applications.JobPostId = $jobPostId,
					Applications.Status = 'Under Review',
					Applications.ApplyDate = TO_DATE('$currentDate','YYYY-MM-DD')
				WHERE Applications.ApplicationId = $applicationId
				");
    executePlainSQL(
        "UPDATE JobPosts 
				SET JobPosts.NumOfApplications = JobPosts.NumOfApplications+1
				WHERE JobPosts.JobPostId = $jobPostId
				");

    if ($success){
        echo "<p style='color: green;'>Application was submitted successfully</p>";
    } else{
        echo "<p style='color: red;'>Fail to submit Application</p>";
    }
    oci_commit($db_conn);
}


function handleSubmitApplicationRequest($jobPostId, $coverLetter, $resume) {
    global $db_conn, $success;
    $jobSeekerUserName = $_SESSION["username"];
    $result = executePlainSQL(
        "SELECT
					JobPosts.JobPostId,
					JobPosts.RecruiterId
				FROM
					JobPosts
				WHERE
					JobPosts.JobPostId = $jobPostId
				");
    $currentDate = date('Y-m-d');
    // check if cover letter and resume are valid link
    if (filter_var($resume, FILTER_VALIDATE_URL) === FALSE) {
        echo "<p style='color: red;'>Invalid Resume URL, please try again!</p>";
        return;
    }
    if (!empty($coverLetter) && filter_var($coverLetter, FILTER_VALIDATE_URL) === FALSE) {
        echo "<p style='color: red;'>Invalid Cover Letter URL, please try again!</p>";
        return;
    }
    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {

        $result = executePlainSQL("SELECT Count(*) AS RESULT
											FROM Resumes
											WHERE Resumes.Resume = '$resume'
											AND Resumes.JobSeekerId = '$jobSeekerUserName'");

        $countResult = OCI_Fetch_Array($result, OCI_ASSOC);
        if ($countResult["RESULT"] == 0){ // this job seeker don't have that resume
            executePlainSQL(
                "INSERT INTO Resumes
						VALUES ('$resume', '$jobSeekerUserName')");
            oci_commit($db_conn);

            if ($success){
                executePlainSQL(
                    "INSERT INTO Applications
							VALUES (ApplicationId_Sequence.nextval, '{$row['RECRUITERID']}', $jobPostId, TO_DATE('$currentDate','YYYY-MM-DD'), '$coverLetter', '$resume', 'Under Review', TO_DATE('$currentDate','YYYY-MM-DD'))");
                oci_commit($db_conn);
                if ($success){
                    executePlainSQL(
                        "UPDATE JobPosts 
									SET JobPosts.NumOfApplications = JobPosts.NumOfApplications+1
									WHERE JobPosts.JobPostId = $jobPostId
									");
                    oci_commit($db_conn);
                } else{
                    executePlainSQL(
                        "DELETE FROM Resumes WHERE Resume='$resume'"
                    );
                    oci_commit($db_conn);
                }

            }else{
                echo "<p style='color: red;'>Fail to submit Application due to duplicate Resume URL</p>";
                return;

            }
        } else{
            executePlainSQL(
                "INSERT INTO Applications
						VALUES (ApplicationId_Sequence.nextval, '{$row['RECRUITERID']}', $jobPostId, TO_DATE('$currentDate','YYYY-MM-DD'), '$coverLetter', '$resume', 'Under Review', TO_DATE('$currentDate','YYYY-MM-DD'))");
            oci_commit($db_conn);
            if ($success){
                executePlainSQL(
                    "UPDATE JobPosts 
								SET JobPosts.NumOfApplications = JobPosts.NumOfApplications+1
								WHERE JobPosts.JobPostId = $jobPostId
								");
                oci_commit($db_conn);
            }
        }

    }

    if ($success){
        echo "<p style='color: green;'>Application was submitted successfully</p>";
    } else{
        echo "<p style='color: red;'>Fail to submit Application</p>";
    }

}

// all routes checker for jobseekers viwing job posts
if (isset($_POST['applyJobPosts']) || isset($_POST['submitApplication']) || isset($_POST['applyWith'])) {
    handlePOSTRequest();
} else if (isset($_GET['displayJobPosts'])){
    handleGETRequest();
}


?>





<hr>
<h2>Find the Average Salary by Job Title</h2>
<form method="GET" action="job_seeker.php">
    <input type="hidden" id="findAvgSalaryByTitleRequest" name="findAvgSalaryByTitleRequest">
    <input type="submit" value="Find" name="findAvgSalaryByTitle">
</form>

<?php
function  handleFindAvgSalaryByTitleRequest(){
    global $db_conn;
    $result = executePlainSQL(
        "SELECT Title, AVG(Salary) AS AvgSalary
				FROM JobPosts
				GROUP BY TITLE"
    );

    $rowsFetched = false;

    echo "<table border='1'>";
    echo "<tr>
					<th>Job Title</th>
					<th>Average Salary</th>
				</tr>";

    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        $rowsFetched = true;
        $avgSalary = $row["AVGSALARY"] ?? "N/A";

        echo "<tr>
						<td>{$row["TITLE"]}</td>
						<td>{$avgSalary}</td>
						</tr>";

    }
    echo "</table>";
    if (!$rowsFetched) {
        echo "<p style='color: blue;'>No results found</p>";
    }

    oci_commit($db_conn);
}
if (isset($_GET['findAvgSalaryByTitle'])){
    handleGETRequest();
}


?>



<hr>
<h2>Find the Average Salary for Each Company That Have More Than One Job Posted</h2>
<form method="GET" action="job_seeker.php">
    <input type="hidden" id="findAvgSalaryByCompanyRequest" name="findAvgSalaryByCompanyRequest">
    <input type="submit" value="Find" name="findAvgSalaryByCompany">
</form>


<?php
function handleFindAvgSalaryByCompanyRequest() {
    global $db_conn;

    $result = executePlainSQL(
        "SELECT C.CompanyId AS CompanyId, C.CompanyName AS CompanyName, AVG(JP.Salary) AS AvgSalary
				FROM Companies C, Recruiters R, JobPosts JP
				WHERE C.CompanyId = R.CompanyId
				  AND R.UserName = JP.RecruiterId
				GROUP BY C.CompanyId, C.CompanyName
				HAVING COUNT(*) > 1
				ORDER BY AvgSalary DESC"
    );

    $rowsFetched = false;

    echo "<table border='1'>";
    echo "<tr>
					<th>Company Id</th>
					<th>Company Name</th>
					<th>Average Salary</th>
				</tr>";

    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        $rowsFetched = true;
        $avgSalary = $row["AVGSALARY"] ?? "N/A";

        echo "<tr>
						<td>{$row["COMPANYID"]}</td>
						<td>{$row["COMPANYNAME"]}</td>
						<td>{$avgSalary}</td>
						</tr>";

    }
    echo "</table>";
    if (!$rowsFetched) {
        echo "<p style='color: blue;'>No results found</p>";
    }

    oci_commit($db_conn);
}

if (isset($_GET['findAvgSalaryByCompany'])){
    handleGETRequest();
}


?>


<hr>
<h2>Find the Company with Recruiters Posting Jobs Exceeding the Overall Average Salary of All Job Posts</h2>
<form method="GET" action="job_seeker.php">
    <input type="hidden" id="findAvgSalaryExceedsOverallAvgRequest" name="findAvgSalaryExceedsOverallAvgRequest">
    <input type="submit" value="Find" name="findAvgSalaryExceedsOverallAvg">
</form>


<?php
function handleFindAvgSalaryExceedsOverallAvgRequest() {
    global $db_conn;

    $result = executePlainSQL(
        "SELECT C.CompanyId AS COMPANYID, C.CompanyName AS COMPANYNAME, AVG(JP.Salary) AS AvgSalary
				FROM Companies C, Recruiters R, JobPosts JP
				WHERE C.CompanyId = R.CompanyId AND R.UserName = JP.RecruiterId
				GROUP BY C.CompanyId, C.CompanyName
				HAVING AVG(JP.Salary) > (SELECT
											 AVG(Salary)
										 FROM JobPosts)"
    );

    $rowsFetched = false;

    echo "<table border='1'>";
    echo "<tr>
					<th>Company Id</th>
					<th>Company Name</th>
					<th>Average Salary</th>
				</tr>";

    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        $rowsFetched = true;
        $avgSalary = $row["AVGSALARY"] ?? "N/A";

        echo "<tr>
						<td>{$row["COMPANYID"]}</td>
						<td>{$row["COMPANYNAME"]}</td>
						<td>{$avgSalary}</td>
						</tr>";

    }
    echo "</table>";
    if (!$rowsFetched) {
        echo "<p style='color: blue;'>No results found</p>";
    }

    oci_commit($db_conn);
}

if (isset($_GET['findAvgSalaryExceedsOverallAvg'])){
    handleGETRequest();
}


?>


<?php
// The following code will be parsed as PHP

function debugAlertMessage($message)
{
    global $show_debug_alert_messages;

    if ($show_debug_alert_messages) {
        echo "<script type='text/javascript'>alert('" . $message . "');</script>";
    }
}

function executePlainSQL($cmdstr)
{ //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;

    $statement = oci_parse($db_conn, $cmdstr);
    //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = oci_execute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); // For oci_execute errors pass the statementhandle
        echo htmlentities($e['message']);
        $success = False;
    }


    return $statement;
}

function executeBoundSQL($cmdstr, $list)
{
    /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
    In this case you don't need to create the statement several times. Bound variables cause a statement to only be
    parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
    See the sample code below for how this function is used */

    global $db_conn, $success;
    $statement = oci_parse($db_conn, $cmdstr);

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
            oci_bind_by_name($statement, $bind, $val);
            unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
        }

        $r = oci_execute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
    }
}


function connectToDB()
{
    global $db_conn;
    global $config;

    // Your username is ora_(CWL_ID) and the password is a(student number). For example,
    // ora_platypus is the username and a12345678 is the password.
    // $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
    $db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

    if ($db_conn) {
        debugAlertMessage("Database is Connected");
        return true;
    } else {
        debugAlertMessage("Cannot connect to Database");
        $e = OCI_Error(); // For oci_connect errors pass no handle
        echo htmlentities($e['message']);
        return false;
    }
}

function disconnectFromDB()
{
    global $db_conn;

    debugAlertMessage("Disconnect from Database");
    oci_close($db_conn);
}





// HANDLE ALL POST ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
function handlePOSTRequest()
{

    if (connectToDB()) {
        if (array_key_exists('submitApplicationRequest', $_POST) && array_key_exists('submitApplication', $_POST)) {
            handleSubmitApplicationRequest($_POST['submitApplication'], $_POST['coverLetter'], $_POST['resume']);
        }else if (array_key_exists('applyJobPostsRequest', $_POST)  && array_key_exists('applyJobPosts', $_POST)) {
            handleApplyJobPostsRequest($_POST['applyJobPosts']);
        } else if (array_key_exists('createDraftApplicationsRequest', $_POST)  && array_key_exists('createDraftApplications', $_POST)) {
            handleCreateDraftApplicationsRequest($_POST['coverLetter'], $_POST['resume']);
        }  else if (array_key_exists('deleteIncompleteApplicationsRequest', $_POST)  && array_key_exists('deleteIncompleteApplications', $_POST)) {
            handleDeleteIncompleteApplicationsRequest($_POST['deleteIncompleteApplications']);
        } else if (array_key_exists('applyWithRequest', $_POST)  && array_key_exists('applyWith', $_POST)) {
            handleApplyWithRequest($_POST['applyWith'], $_POST['selectedApplication']);
        } else if (array_key_exists('deleteResumesRequest', $_POST)  && array_key_exists('deleteResumes', $_POST)) {
            handleDeleteResumesRequest($_POST['deleteResumes']);
        }

        disconnectFromDB();
    }
}


// HANDLE ALL GET ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
function handleGETRequest()
{
    if (connectToDB()) {
        if (array_key_exists('displayJobPosts', $_GET)){
            handleDisplayJobPostsRequest();
        }elseif (array_key_exists('displayIncompleteApplications', $_GET)){
            handleDisplayIncompleteApplicationsRequest();
        }elseif (array_key_exists('displayResumes', $_GET)){
            handleDisplayResumesRequest();
        }elseif (array_key_exists('findAvgSalaryByTitle', $_GET)){
            handleFindAvgSalaryByTitleRequest();
        }elseif (array_key_exists('findAvgSalaryByCompany', $_GET)){
            handleFindAvgSalaryByCompanyRequest();
        }elseif (array_key_exists('findAvgSalaryExceedsOverallAvg', $_GET)){
            handleFindAvgSalaryExceedsOverallAvgRequest();
        }
        disconnectFromDB();
    }
}



// End PHP parsing and send the rest of the HTML content
?>
</body>

</html>
