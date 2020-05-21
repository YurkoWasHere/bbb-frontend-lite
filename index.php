<?php
$secret = "----";
$hostname="bbb.hypha.coop";

// Creates URL (including checksum) used interact with API includin
function getBBBAPIurl($secret, $apiName, $param)
{
    $URL = "https://$hostname/bigbluebutton/api/";
    $apiString = http_build_query($param);
    $checksumString = $apiName . $apiString . $secret;
    $checksum = sha1($checksumString);
    $apiString .= "&checksum=" . $checksum;
    return   $URL . $apiName . "?" . $apiString;
}

// GETS result after getBBBAPIurl
function callBBBAPI($secret, $apiName, $param)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,  getBBBAPIurl($secret, $apiName, $param));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $value = curl_exec($ch);
    return  $value;
}


if ($_POST['action'] == "Join") {

    // Get meeting information
    $meeting = $_POST['meetingname'];
    $name = $_POST['yourname'];

    // Prepare sanatized version for comparison
    $meetingSanitized = preg_replace("/[^a-zA-Z0-9]+/", "", $meeting);
    $nameSanitized = preg_replace("/[^a-zA-Z0-9]+/", "", $name);

    // Check if meeting name and name is not empty and contains valid characters only
    if ($meeting != "" && $name != "" && $meetingSanitized == $meeting && $nameSanitized == $name) {

        //Create room
        $param = array(
            "meetingID" => $meeting,
            "attendeePW" => "none", //Must have password so make it NONE
            "moderatorPW" => "admin"
        );

        $res = callBBBAPI($secret, "create", $param);
        // ##TODO## success check
        $res = simplexml_load_string($res);

        //If no one in room make first person moderator by using moderator password, otherwise use attendee password
        $password = "none";
        if ($res['hasUserJoined'] == 0) $password = "admin"; //make first user moderator


        $param = array(
            "fullName" => $name,
            "meetingID" => $meeting,
            "password" => $password
        );

        // Get url client should goto to join room
        $res = getBBBAPIurl($secret, "join", $param);
        // Redirect client to the room
        header("location: $res");
    }
}
?>
<html>

<head>
    <style>
        body {
            background: linear-gradient(-90deg, #1251AE 0, #0074FF 50%, #1251AE 100%);
            text-align: center;
            color: white;
            font-family: -apple-system, BlinkMacSystemFont, open_sanslight, "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        form {
            background: #ffffff;
            color: black;
            display: block;
            max-width: calc(100% - 40px);
            width: 680px;
            padding: 20px;
            margin-left: calc(50% - 340px);
            box-shadow: rgba(0, 0, 0, .1) 0 1rem 1rem;
            text-align: left;
        }

        input[type="text"] {
            outline: 0;
            resize: none;
            border: none;
            display: inline-block;
            width: 100%;
            font-size: 14px;
            border-bottom: 1px solid #cccccc;
        }

        input[type="submit"] {
            border: 0px;
            width: 51px;
            min-width: inherit;
            height: 35px;
            font-size: 14px;
            font-weight: inherit;
            background: #0074E0;
            border-radius: 4px;
            color: #FFF;
            text-align: center;
            vertical-align: middle;
            line-height: 35px;
            cursor: pointer;
        }
    </style>

<body>
    <img src="https://raw.githubusercontent.com/bigbluebutton/greenlight/master/app/assets/images/logo_with_text.png" style="float:left;"><br><br><br><br>
    <h1>Start a BBB confrence call</h1>
    <div style=" width:680px;margin-left:calc(50% - 340px);font-weight: 400; line-height: 24px;">
        Go ahead, video chat with the whole team. In fact, invite everyone you know. BBB is a open source video conferencing solution that you can use all day, every day, for free — with no account needed.
    </div>
    <br>

    <form method="post">
        <span style="font-size:18px; color: #253858;font-weight:bold;"><br>
            Start a new meeting called</span><br><br>
        <input type="text" value="<?= $_REQUEST['meetingname'] ?>" name="meetingname"><br>
        <br>
        <br>
        <span style="font-size:18px; color: #253858;font-weight:bold;">Your Name</span><br><input type="text" value="" name="yourname"><br>
        <br><br>
        <center> <input type="submit" value="Join" name="action"></center>
    </form>
</body>

</html>
