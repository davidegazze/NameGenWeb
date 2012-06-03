<?php

if (isset($_GET['page']))	{
	
	if($_GET['page'] == "phase1") {
		
	?>	
		<span style="padding-right:5px;">
		  <a class="topbutton" role="button" onClick="toggleContent('1')">Get Started
		   </a>   
		</span>
		<span style="text-decoration:none;display:inline-block;font-size:14px;padding-top:5px">
		  or <a class="infolink" href="learnmore.php">Learn More</a>
		</span>			
		
<?	} else { ?>
	
	<span style="padding-right:5px;">
	  <a class="topbutton" role="button" href="index.php?page=phase1">Start Again</a>   
	</span>
	<span style="padding-right:5px;">
	  or <a class="infolink" role="button" href="learnmore.php">Learn More</a>   
	</span>
	
<?	} ?>
	
<? } else { ?>
	
	<span style="padding-right:5px;">
	  <a class="topbutton" role="button" onClick="toggleContent('1')">Get Started
	   </a>   
	</span>
	<span style="text-decoration:none;display:inline-block;font-size:14px;padding-top:5px">
	  or <a class="infolink" href="learnmore.php">Learn More</a>
	</span>	
	
<? } ?>
