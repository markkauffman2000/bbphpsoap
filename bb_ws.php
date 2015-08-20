<?php
/*
 *  bb_ws-php - An example script accessing the Blackboard Learn 9 web services using PHP
 *  Copyright (C) 2013  Stephen P Vickers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Contact: stephen@spvsoftwareproducts.com
 *
 * Version history:
 *   1.0.00  10-Feb-13  Initial version
 *   1.1.00  27-Apr-13  Added members option to retrieve course memberships
 *                      Allow course and user options to list multiple IDs
 *   mbk     14-Aug-15  Hacked in addcolumn to create a column, save a grade there, and fetch it.
*/

/*
 # http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
*/

function objectToArray($d) {
 if (is_object($d)) {
 // Gets the properties of the given object
 // with get_object_vars function
 $d = get_object_vars($d);
 }
 
 if (is_array($d)) {
 /*
 * Return array converted to object
 * Using __FUNCTION__ (Magic constant)
 * for recursive call
 */
 return array_map(__FUNCTION__, $d);
 }
 else {
 // Return array
 return $d;
 }
}// end function objectToArray 

 function arrayToObject($d) {
 if (is_array($d)) {
 /*
 * Return array converted to object
 * Using __FUNCTION__ (Magic constant)
 * for recursive call
 */
 return (object) array_map(__FUNCTION__, $d);
 }
 else {
 // Return object
 return $d;
 }
} // end function arrayToObject

/* 
 * Create courseObject with the same member variables as a CourseVO that we can use later in the code to build courses.
*/
$int0 = 0;
settype($int0, "int");
$intneg1 = -1;
settype($intneg1, "int");
$double0 = 0;
settype($double0, "double");

$courseObjectArray = array(
  'allowGuests' => true,
  'allowObservers' => 0,
  'available' => true,
  'batchUid' => "MBK-300",
  'buttonStyleBbId' =>  "_512_1",
  'buttonStyleShape' =>  "Default",
  'buttonStyleType' =>  "Default",
// 'cartridgeId' => NULL,
  'classificationId' =>  "_113_1",
  'courseDuration' =>  "Continuous",
  'courseId' =>  "MBK-300",
  'coursePace' =>  "InstructorLed",
  'courseServiceLevel' =>  "Course",
  'dataSourceId' =>  "_2_1",
  'decAbsoluteLimit' => $int0,
  'description' =>  "Mark Kauffman's 300 Test Course",
  'endDate' => $int0,
  'enrollmentAccessCode' =>  "",
  'enrollmentEndDate' => $int0,
  'enrollmentStartDate' => $int0,
  'enrollmentType' =>  "InstructorLed",
//  'expansionData' =>  "COURSE.UUID=b72cefac9c3b435d913efb5411e62eef",
  'fee' => $double0,
  'hasDescriptionPage' => 0,
//  'id' =>  "_1281_1",
  'institutionName' =>  "",
  'locale' =>  "en_US",
  'localeEnforced' => 0,
  'lockedOut' => 0,
  'name' =>  "Mark Kauffman's 300 Test Oceanography",
  'navCollapsable' => true,
  'navColorBg' =>  "#336699",
  'navColorFg' =>  "#FFFFFF",
  'navigationStyle' =>  "Text",
  'numberOfDaysOfUse' => $intneg1,
  'organization' => 0,
  'showInCatalog' => true,
  'softLimit' => $int0,
  'startDate' => $int0,
  'uploadLimit' => $int0
);
/*
$courseObject = arrayToObject($courseObjectArray);
$courseObject->batchUid = "mbk-php1";
$courseObject->courseId = "mbk-php1"; 
$courseObject->description = "kauffman's php creation";
$courseObject->id = "";
$courseObject->name = "Mark Kauffman's PHP Creation 101";
*/

/*
print "--- current course object array ----\n";
print_r($courseObjectArray);
print "---- end current course object array ----\n";
*/

// CREATE A GRADE/SCORE OBJECT this belongs to mkauffman in the course _1288_1. You will need to code this to be
// general, find the users course member id in a specific course and use that.
  $myScoreVO = new stdClass();
  $myScoreVO->averageScore = (double) 0;
  $myScoreVO->columnId = "_3507_1"; // column named blah44
  $myScoreVO->courseId = NULL;
  $myScoreVO->exempt = 0; //false
  $myScoreVO->expansionData = NULL;
  $myScoreVO->firstAttemptId = NULL;
  $myScoreVO->grade = "22";
  $myScoreVO->highestAttemptId = NULL;
//  $myScoreVO->id = "_1635_1";
  $myScoreVO->instructorComments = NULL;
  $myScoreVO->lastAttemptId = NULL;
  $myScoreVO->lowestAttemptId = NULL;
  $myScoreVO->manualGrade = "22";
  $myScoreVO->manualScore = (double)22;
  $myScoreVO->memberId = "_2121_1";
  $myScoreVO->schemaGradeValue = "22.00";
  $myScoreVO->shortInstructorComments = NULL;
  $myScoreVO->shortStudentComments = NULL;
  $myScoreVO->status = (int)1;
  $myScoreVO->studentComments = NULL;
  $myScoreVO->userId = NULL;

// START Stephen's Original Code

// Load configuration settings
  require_once('config.php');

// Load dependent library files
  require_once('lib.php');

// Print header
  print 'Learn 9 server: ' . SERVER_URL . "\n";
  print 'Proxy tool:     ' . VENDOR_ID . '/' . PROGRAM_ID . "\n\n";

  $ok = isset($argv[1]);
  $err = FALSE;

  if ($ok) {

// Initialise a Context SOAP client object
    try {
      $context_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Context.WS?wsdl');
    } catch (Exception $e) {
      $ok = FALSE;
      print "ERROR: {$e->getMessage()}\n";
    }

  }

  if ($ok) {

    $action = strtolower($argv[1]);

    if ($action == 'register') {
##
#### Register the tool
##
      print 'Registering tool... ';
      $register_tool = new stdClass();
      $register_tool->clientVendorId = VENDOR_ID;
      $register_tool->clientProgramId = PROGRAM_ID;
      $register_tool->registrationPassword = REGISTRATION_PASSWORD;
      $register_tool->description = TOOL_DESCRIPTION;
      $register_tool->initialSharedSecret = SHARED_SECRET;
      $register_tool->requiredToolMethods =   array("Announcement.WS:createCourseAnnouncements",
                                                              "Announcement.WS:createOrgAnnouncements",
                                                              "Announcement.WS:createSystemAnnouncements",
                                                              "Announcement.WS:deleteCourseAnnouncements",
                                                              "Announcement.WS:deleteOrgAnnouncements",
                                                              "Announcement.WS:deleteSystemAnnouncements",
                                                              "Announcement.WS:getCourseAnnouncements",
                                                              "Announcement.WS:getOrgAnnouncements",
                                                              "Announcement.WS:getSystemAnnouncements",
                                                              "Announcement.WS:updateCourseAnnouncements",
                                                              "Announcement.WS:updateOrgAnnouncements",
                                                              "Announcement.WS:updateSystemAnnouncements",
                                                              "Calendar.WS:canUpdateCourseCalendarItem",
                                                              "Calendar.WS:canUpdateInstitutionCalendarItem",
                                                              "Calendar.WS:canUpdatePersonalCalendarItem",
                                                              "Calendar.WS:createCourseCalendarItem",
                                                              "Calendar.WS:createInstitutionCalendarItem",
                                                              "Calendar.WS:createPersonalCalendarItem",
                                                              "Calendar.WS:deleteCourseCalendarItem",
                                                              "Calendar.WS:deleteInstitutionCalendarItem",
                                                              "Calendar.WS:deletePersonalCalendarItem",
                                                              "Calendar.WS:getCalendarItem",
                                                              "Calendar.WS:saveCourseCalendarItem",
                                                              "Calendar.WS:saveInstitutionCalendarItem",
                                                              "Calendar.WS:savePersonalCalendarItem",
                                                              "Calendar.WS:updateCourseCalendarItem",
                                                              "Calendar.WS:updateInstitutionCalendarItem",
                                                              "Calendar.WS:updatePersonalCalendarItem",
                                                              "Content.WS:addContentFile",
                                                              "Content.WS:deleteContentFiles",
                                                              "Content.WS:deleteContents",
                                                              "Content.WS:deleteCourseTOCs",
                                                              "Content.WS:deleteLinks",
                                                              "Content.WS:getContentFiles",
                                                              "Content.WS:getFilteredContent",
                                                              "Content.WS:getFilteredCourseStatus",
                                                              "Content.WS:getLinksByReferredToType",
                                                              "Content.WS:getLinksByReferrerType",
                                                              "Content.WS:getReviewStatusByCourseId",
                                                              "Content.WS:getTOCsByCourseId",
                                                              "Content.WS:loadContent",
                                                              "Content.WS:removeContent",
                                                              "Content.WS:saveContent",
                                                              "Content.WS:saveContentsReviewed",
                                                              "Content.WS:saveCourseTOC",
                                                              "Content.WS:saveLink",
                                                              "Context.WS:emulateUser", 
                                                              "Context.WS:getMemberships", 
                                                              "Context.WS:getMyMemberships",
                                                              "Course.WS:changeCourseBatchUid",
                                                              "Course.WS:changeCourseCategoryBatchUid",
                                                              "Course.WS:changeCourseDataSourceId",
                                                              "Course.WS:changeOrgBatchUid",
                                                              "Course.WS:changeOrgCategoryBatchUid",
                                                              "Course.WS:changeOrgDataSourceId",
                                                              "Course.WS:createCourse",
                                                              "Course.WS:createOrg",
                                                              "Course.WS:deleteCartridge",
                                                              "Course.WS:deleteCourse",
                                                              "Course.WS:deleteCourseCategory",
                                                              "Course.WS:deleteCourseCategoryMembership",
                                                              "Course.WS:deleteGroup",
                                                              "Course.WS:deleteOrg",
                                                              "Course.WS:deleteOrgCategory",
                                                              "Course.WS:deleteOrgCategoryMembership",
                                                              "Course.WS:deleteStaffInfo",
                                                              "Course.WS:getAvailableGroupTools",
                                                              "Course.WS:getCartridge",
                                                              "Course.WS:getCategories",
                                                              "Course.WS:getCategoryMembership",
                                                              "Course.WS:getClassifications",
                                                              "Course.WS:getCourse",
                                                              "Course.WS:getGroup",
                                                              "Course.WS:getOrg",
                                                              "Course.WS:getStaffInfo",
                                                              "Course.WS:saveCartridge",
                                                              "Course.WS:saveCourse",
                                                              "Course.WS:saveCourseCategory",
                                                              "Course.WS:saveCourseCategoryMembership",
                                                              "Course.WS:saveGroup",
                                                              "Course.WS:saveOrgCategory",
                                                              "Course.WS:saveOrgCategoryMembership",
                                                              "Course.WS:saveStaffInfo",
                                                              "Course.WS:updateCourse",
                                                              "Course.WS:updateOrg",
                                                              "Course.WS:loadCoursesInTerm", 
                                                              "Course.WS:addCourseToTerm", 
                                                              "Course.WS:removeCourseFromTerm", 
                                                              "Course.WS:loadTerm", 
                                                              "Course.WS:loadTermByCourseId", 
                                                              "Course.WS:saveTerm", 
                                                              "Course.WS:removeTerm", 
                                                              "Course.WS:loadTerms", 
                                                              "Course.WS:loadTermsByName",
                                                              "CourseMembership.WS:deleteCourseMembership",
                                                              "CourseMembership.WS:deleteGroupMembership",
                                                              "CourseMembership.WS:getCourseMembership",
                                                              "CourseMembership.WS:getCourseRoles",
                                                              "CourseMembership.WS:getGroupMembership",
                                                              "CourseMembership.WS:saveCourseMembership",
                                                              "CourseMembership.WS:saveGroupMembership",
                                                              "Gradebook.WS:deleteAttempts",
                                                              "Gradebook.WS:deleteColumns",
                                                              "Gradebook.WS:deleteGradebookTypes",
                                                              "Gradebook.WS:deleteGrades",
                                                              "Gradebook.WS:deleteGradingSchemas",
                                                              "Gradebook.WS:getAttempts",
                                                              "Gradebook.WS:getGradebookColumns",
                                                              "Gradebook.WS:getGradebookTypes",
                                                              "Gradebook.WS:getGrades",
                                                              "Gradebook.WS:getGradingSchemas",
                                                              "Gradebook.WS:saveAttempts",
                                                              "Gradebook.WS:saveColumns",
                                                              "Gradebook.WS:saveGradebookTypes",
                                                              "Gradebook.WS:saveGrades",
                                                              "Gradebook.WS:saveGradingSchemas",
                                                              "Gradebook.WS:updateColumnAttribute",
                                                              "User.WS:changeUserBatchUid",
                                                              "User.WS:changeUserDataSourceId",
                                                              "User.WS:deleteAddressBookEntry",
                                                              "User.WS:deleteObserverAssociation",
                                                              "User.WS:deleteUser",
                                                              "User.WS:deleteUserByInstitutionRole",
                                                              "User.WS:getAddressBookEntry",
                                                              "User.WS:getInstitutionRoles",
                                                              "User.WS:getObservee",
                                                              "User.WS:getSystemRoles",
                                                              "User.WS:getUser",
                                                              "User.WS:getUserInstitutionRoles",
                                                              "User.WS:saveAddressBookEntry",
                                                              "User.WS:saveObserverAssociation",
                                                              "User.WS:saveUser",
                                                              "Util.WS:checkEntitlement",
                                                              "Util.WS:deleteSetting",
                                                              "Util.WS:getDataSources",
                                                              "Util.WS:loadSetting",
                                                              "Util.WS:saveSetting");
      try {
        $result = $context_client->registerTool($register_tool);
        $ok = $result->return->status;
        if ($ok) {
          print "Success (now make the proxy tool available in Learn 9)\n";
        } else {
          $err = TRUE;
          print "Failed (may already be registered)\n";
        }
      } catch (Exception $e) {
        $err = TRUE;
        print "ERROR: {$e->getMessage()}\n";
      }

    } else {

// Get a session ID
      try {
        $result = $context_client->initialize();
        $password = $result->return;
      } catch (Exception $e) {
        $err = TRUE;
        print "ERROR: {$e->getMessage()}\n";
      }

      if (!$err) {

// Log in as a tool
        $input = new stdClass();
        $input->password = SHARED_SECRET;
        $input->clientVendorId = VENDOR_ID;
        $input->clientProgramId = PROGRAM_ID;
        $input->loginExtraInfo = '';  // not used but must not be NULL
        $input->expectedLifeSeconds = 3600;
        try {
          $result = $context_client->loginTool($input);
        } catch (Exception $e) {
          $err = TRUE;
          print "ERROR: {$e->getMessage()}\n";
        }
      }

      if (!$err) {

        if ($action == 'courses') {
##
#### Get courses for a user
##
          $ok = isset($argv[2]);
          if ($ok) {
            print 'Retrieving courses...';

            $member = new stdClass();
            $member->userid = $argv[2];
            try {
              $result = $context_client->getMemberships($member);
              for ($i = 0; $i < count($result->return); $i++) {
                print " {$result->return[$i]->externalId}";
              }
              print "\n";
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }

          }

        } else if ($action == 'course') {
##
#### Get course details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a Course SOAP client object
            try {
              $course_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Course.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving course(s)... ';
            $ids = array();
            for ($i = 2; $i < $argc; $i++) {
              $ids[] = $argv[$i];
            }
            $course = new stdClass();
            $course->filter = new stdClass();
            if (substr($argv[2], 0, 1) == '_') {
              $course->filter->ids = $ids;
              $course->filter->filterType = 3;
            } else {
              $course->filter->courseIds = $ids;
              $course->filter->filterType = 1;
            }
            try {
              $results = $course_client->getCourse($course);
              $ok = $results->return;
              if ($ok) {
                print "\n";
                $courses = $results->return;
                if (!is_array($courses)) {
                  $courses = array();
                  $courses[] = $results->return;
                }
                for ($i = 0; $i < count($courses); $i++) {
                  $result = $courses[$i];
                  if ($course->filter->filterType == 3) {
                    print "  {$result->id}: {$result->name} ({$result->courseId})\n";
                  } else {
                    print "  {$result->courseId}: {$result->name} ({$result->id})\n";
                  }
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }
        } else if ($action == 'createcourse') {
##
#### Set course details
##
          // $ok = isset($argv[2]); for now we just use the course object as is.

          if ($ok) {
// Initialise a Course SOAP client object
            try {
              $course_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Course.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Creating course... ';

            try {
              $result = $course_client->createCourse($courseObjectArray);
              $ok = $result;

              if ($ok) { // then our course was created
                print "id of created course:{$result} \n";           
              } else {
                $err = TRUE;
                print "Couldn't create course.\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }// end if $ok Creating course...

        } else if ($action == 'user') {
##
#### Get user details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a User SOAP client object
            try {
              $user_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/User.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving user(s)... ';
            $ids = array();
            for ($i = 2; $i < $argc; $i++) {
              $ids[] = $argv[$i];
            }
            $user = new stdClass();
            $user->filter = new stdClass();
            if (substr($argv[2], 0, 1) == '_') {
              $user->filter->id = $ids;
              $user->filter->filterType = 2;
            } else {
              $user->filter->name = $ids;
              $user->filter->filterType = 6;
            }
            try {
              $results = $user_client->getUser($user);
              $ok = $results->return;
              if ($ok) {
                $users = $results->return;
                print "\n";
                if (!is_array($users)) {
                  $users = array();
                  $users[] = $results->return;
                }
                for ($i = 0; $i < count($users); $i++) {
                  $result = $users[$i];
                  if ($user->filter->filterType == 2) {
                    print "  {$result->id}: {$result->extendedInfo->givenName} {$result->extendedInfo->familyName} ({$result->name})\n";
                  } else {
                    print "  {$result->name}: {$result->extendedInfo->givenName} {$result->extendedInfo->familyName} ({$result->id})\n";
                  }
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }
        } else if ($action == 'addcolumn') {
##
#### add a column to the gradebook for _1288_1: mbk-2015-01-partner-b2tests
#### then save a grade there, READ the myScoreVO def at the top of this code.
###  then fetch the grade back
#### WHEN YOU RUN THIS AGAINST YOUR SERVER you will need to input a userId and courseId that exist on your server.
#### after we add the column, we get it, then we add a grade to it.
##
          $ok = (isset($argv[2]) && isset($argv[3]) && isset($argv[4])); // need courseId userId and newcolumnName

          if ($ok) {
// Initialise a Gradebook SOAP client object
            try {
              $gradebook_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Gradebook.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
            $courseId=$argv[2];
            $userId=$argv[3];
            $columnname=$argv[4];
          }

          $params = array();
          $params['courseId'] = $courseId;
          $params['columns'] = array(
            'columnName' => $columnname,
            'possible' => "100.0",
            "scorable"=> "true",
            "showStatsToStudent"=> "true",
            "visible"=> "true",
            "visibleInBook"=> "true"
            );

          $result = $gradebook_client->saveColumns( $params );
          print "saveColumns result:\n";         
          var_dump(get_object_vars($result));
          $colid = $result->return;
          print "colid is:{$colid}\n";
	  print "End new column Id:\n";
          
          // Now add a grade for a user. user Id mkauffman: Mark Kauffman (_1425_1)
          // const GET_SCORE_BY_COLUMN_ID_AND_USER_ID = 2; // http://library.blackboard.com/ref/72b6dc32-b778-4074-a670-4baed55b21f5/constant-values.html#blackboard.ws.gradebook.GradebookWSConstants.GET_SCORE_BY_MEMBER_ID_AND_COLUMN_ID
          // const GET_SCORE_BY_COURSE_ID_AND_COLUMN_ID = 3; // http://library.blackboard.com/ref/72b6dc32-b778-4074-a670-4baed55b21f5/constant-values.html#blackboard.ws.gradebook.GradebookWSConstants.GET_SCORE_BY_MEMBER_ID_AND_COLUMN_ID
         // const GET_SCORE_BY_ID = 7
         // NOTE: column_id and id are DIFFERENT.

/* IGNORE This is a hard-coded fetch of a saved score on a particular system.
          // first we get a previous grade/score. this is hard coded.
          // the result will not be null IF we get a grade that we previously set.
// IGNORE this column filter. were' not using it for this example.
          $columnFilter = new stdClass();
          $columnFilterType = 1; // GET_COLUMN_BY_COURSE_ID
          $columnFilter->columnFilterType = $columnFilterType;
          $columnFilters=array($columnFilter);
          
          $scoreFilter = new stdClass();
//          $scoreFilter->columnFilters = $columnFilters;
          $scoreFilter->columnId = "_3507_1";
          $scoreFilter->filterType = 3; // GET_SCORE_BY_COLUMN_ID

          $params = array();
          $params['courseId'] = '_1288_1';
          
          $params['filter'] = $scoreFilter;

          $result = $gradebook_client->getGrades($params );
          print "getGrades result:\n";         
          var_dump(get_object_vars($result));
// END IGNORES
*/
           
// NOW we add the grade to the column we created above.

          $myScoreVO->columnId = $colid; // this should save the score in our new column. 
          $myScoreVO->memberId = NULL;
          $myScoreVO->userId =  $userId;
          $gradesArray = array($myScoreVO);
          $params = array();
          $params['courseId'] = $courseId;
          $params['grades'] = $gradesArray;
          $params['overrideIfManual'] = TRUE;
          $result = $gradebook_client->saveGrades($params);
          print "saveGrades result:\n";
          var_dump(get_object_vars($result));
          $new_id = $result->return; // THIS IS NOT the column_id.
          print "new_id: {$new_id}\n";

// now demonstrate getting the grade that we just saved!

          $scoreFilter = new stdClass();
          $scoreFilter->id = $new_id;
          $scoreFilter->filterType = 7; // GET_SCORE_BY_ID

          $params = array();
          $params['courseId'] = '_1288_1';
          
          $params['filter'] = $scoreFilter;

          $result = $gradebook_client->getGrades($params );
          print "2nd getGrades result:\n";         
          var_dump(get_object_vars($result));
           
        } else if ($action == 'member') {
##
#### Get course membership details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a User SOAP client object
            try {
              $membership_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/CourseMembership.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving membership(s)... ';
            $course = new stdClass();
            $course->courseId = $argv[2];
            $ids = array();
            if ($argc >= 4) {
              for ($i = 3; $i < $argc; $i++) {
                $ids[] = $argv[$i];
              }
            } else {
              $ids[] = '';
            }
            $member = new stdClass();
            $member->courseId = $argv[2];
            $member->f = new stdClass();
            $member->f->userIds = $ids;
            $member->f->filterType = 6;
            try {
              $results = $membership_client->getCourseMembership($member);
              $ok = $results->return;
              if ($ok) {
                $memberships = $results->return;
                print "\n";
                if (!is_array($memberships)) {
                  $memberships = array();
                  $memberships[] = $results->return;
                }
                for ($i = 0; $i < count($memberships); $i++) {
                  $result = $memberships[$i];
                  print "  {$result->id}: userId {$result->userId}, enrolled " . date('d-M-y H:i:s', $result->enrollmentDate) . ' (';
                  if (!$result->available) {
                    print 'not ';
                  }
                  print "available)\n";
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

        } else {

          $ok = FALSE;

        }

      }

    }

  }

  if (!$ok && !$err) {
##
#### Display usage information
##
    print "Usage:\n";
    print "  {$argv[0]} register                    -- register the proxy tool\n";
    print "  {$argv[0]} courses {username}          -- get course list for a user\n";
    print "  {$argv[0]} course {course}+            -- get course details (id/courseId)\n";
    print "  {$argv[0]} addcolumn {courseId} {userId} {newcolumnname}      <-- READ THE CODE. DOES MORE THAN THIS\n";
    print "  {$argv[0]} user {user}+                -- get user details (id/username)\n";
    print "  {$argv[0]} member {courseId} {userId}* -- get course membership details\n";

  }

  /*
              if ($ok) {
                $newcourse = new stdClass();
                $newcourse->course_id = $argv[2];

                $ids = array();
                for ($i = 2; $i < $argc; $i++) {
                  $ids[] = $argv[$i];
                }
                $course = new stdClass();
                $course->filter = new stdClass();
                if (substr($argv[2], 0, 1) == '_') {
                  $course->filter->ids = $ids;
                  $course->filter->filterType = 3;
                } else {
                  $course->filter->courseIds = $ids;
                  $course->filter->filterType = 1;
                }

                print "\n";
                $courses = $results->return;
                if (!is_array($courses)) {
                  $courses = array();
                  $courses[] = $results->return;
                }
                for ($i = 0; $i < count($courses); $i++) {
                  $result = $courses[$i];
                  if ($course->filter->filterType == 3) {
                    print "  {$result->id}: {$result->name} ({$result->courseId})\n";
                  } else {
                    print "  {$result->courseId}: {$result->name} ({$result->id})\n";
                  }
#### mbk
      print "------ COURSE OBJECT -----\n";
                  var_dump(get_object_vars($result));
      print "\n";
      $result_array = objectToArray($result);
      print "------ COURSE OBJECT ARRAY -----\n";
      var_dump($result_array);
      print "\n";

                  $newObject = arrayToObject($result_array);
                  
      print "------ REBUILT COURSE OBJECT -----\n";
                  var_dump(get_object_vars($newObject));
      print "\n";                 
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }// was if ok, creating course...will be if ok, getting the course.. after we create it.
*/

?>
