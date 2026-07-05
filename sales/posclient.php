<?php
include('include.php');

header('Content-Type: application/x-java-jnlp-file');
$user = getUser();
$pwd = $_SESSION['pwd'];
?>

<jnlp spec="0.2 1.0"
      codebase="<?php echo getCodebase() ?>">
    <information>
       <title>thERP</title>
       <vendor>TherpSoft</vendor>
       <description>thERP POS client</description>
       <offline-allowed/>
    </information>
    <resources>
       <j2se version="1.4+"/>
       <jar href="java/posclient.jar"/>
       <jar href="java/therputil.jar"/>
       <jar href="java/lib/tablelayout.jar"/>
    </resources>
    <application-desc main-class="therp.pos.POSForm">
       <argument>-1</argument>
       <argument><?php echo $user ?></argument>
       <argument><?php echo $pwd ?></argument>
       <argument><?php echo getCodebase() ?></argument>
    </application-desc>
</jnlp>
