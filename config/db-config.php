<?php

mysql_connect("localhost", "???", "???") or die(mysql_error());
mysql_select_db("namegen") or die(mysql_error());
mysql_query('SET NAMES utf8');
?>