<?php

if (isset($_POST['sourceDB']) && isset($_POST['sourceTable']) && isset($_POST['mode']) && isset($_POST['destDB']))
    echo $_POST['sourceDB'].$_POST['sourceTable'].$_POST['mode'].$_POST['destDB'];
?>