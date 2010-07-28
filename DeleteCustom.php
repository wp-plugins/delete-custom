<?php
/*
Plugin Name: DeleteCustom
Plugin Script: DeleteCustom.php
Plugin URI: http://sisifodichoso.org/proyectos/deletecustom/
Description: Delete your Custom Fields 
Version: 1.1
License: GPL
Author: Marta Garabatos
Author URI: http://sisifodichoso.org

=== CHANGES ===
- v1.1 - Fixed Not wp-prefix bug 
 
- v1.0 - first version
*/


/*  Copyright 2006  Marta Garabatos  (email : sisifodichoso@ya.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/


/*******Globals*******/

$namefield; 

/******Functions******/
function DC_ShowSelect(){
	global $wpdb;
	$dbprefix=$wpdb->prefix;	
	$option='';
	$keys = array();

	//The list of custom fields
	$keys = $wpdb->get_col('SELECT meta_key FROM ' . $dbprefix . 'postmeta ORDER BY meta_key');	
	if ($keys){
		$keys = array_unique($keys); //We only want an unique entry for each field
		foreach ($keys as $key) {
			$option .= "\n\t\t<option value='$key'>$key</option>";
		}
	}
	else { //No custom fields
		echo '<div id="message" class="updated fade"><p><strong>I cannot find any Custom Field in this blog! </strong></p></div>'; 		
	}

	echo <<<EOT
		<div class="wrap">
		<h2>DeleteCustom</h2>
		<h3>Step 1: select</h3>
		<p>Please, select a Custom Field to delete. <br />Feel free to select any field you want, you are not going to delete anything at this moment.</p>		
		<form name="selectfieldform" method="post" action="#">
		<table cellspacing="3" cellpadding="3">
			<tr valign="top">
			<td>
			<select id="metakeyselect" name="metakeyselect" tabindex="7">
			<option value="">Select a Custom Field:</option>
			{$option}
			</select> 
			</td>
			</tr>
		</table>
			<p class="submit"><input type="submit" name="selectfield" value="Select Custom Field &raquo;" />
				</p>
		</form>
		</div>
EOT;
}

function DC_ShowDelete($namefield){
	global $wpdb;	
	$dbprefix=$wpdb->prefix;
	/*global $namefield;
	$namefield = $_POST['metakeyselect'];*/
	$ids = array();
	$guids = array();
	$titles = array();
	$dates = array();
	$values = array();
	$class='';
	$ids = $wpdb->get_col('SELECT post_id FROM ' . $dbprefix . 'postmeta WHERE meta_key = ' . "'" . $namefield . "'");
	echo '<div class="wrap">';			
	echo '<h2>DeleteCustom</h2>';
	echo '<h3>Step 2: delete</h3>';
	echo '<p>Now, you are going to delete the Custom Field called: <strong>"' . $namefield . '" </strong> from some posts associated with it. <br />'; 
	echo 'Please, select the posts on which you want to delete it. <br /></p>';
	
	//Reversing selection
	echo '<script type="text/javascript">
		<!--
		function checkAll(form){
			for (i = 0, n = form.elements.length; i < n; i++) {
				if(form.elements[i].type == "checkbox") {
					if(form.elements[i].checked == true)
						form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
				}
			}
		}
		//-->
		</script>';
	echo '<form id="deletefieldform" name="deletefieldform" method="post" action="#">';
	echo '<table id="the-list-x" width=100%  cellspacing="3" cellpadding="3">';
	echo '<tr>
		<th scope="col">ID</th>
		<th scope="col">Date</th>
		<th scope="col" width="50%">Title</th>
		<th scope="col">"' . $namefield . '" value</th>
		<th scope="col">Delete CF</th> 
 	 </tr>';
			

	if ($ids){
		foreach ($ids as $id){
			$titles [$id] = $wpdb->get_var('SELECT post_title FROM ' . $dbprefix . 'posts WHERE ID =' . $id );
			$guids [$id] = $wpdb->get_var('SELECT guid FROM ' . $dbprefix . 'posts WHERE ID =' . $id );
			$dates [$id] = $wpdb->get_var('SELECT post_date FROM ' . $dbprefix . 'posts WHERE ID =' . $id );
			$values [$id] = $wpdb->get_var('SELECT meta_value FROM ' . $dbprefix . 'postmeta WHERE meta_key ="'. $namefield .'" AND post_id =' . $id );
			$class = ('alternate' == $class) ? '' : 'alternate';
			echo '<tr class="' . $class. '">
				<th scope="row">' . $id .'</th>
				<td>' . $dates [$id] .'</td>
				<td><a href="' . $guids [$id]. '">' . $titles [$id] . '</a></td>
				<td>' . $values [$id] .'</td>
				<th scope="row"><input type="checkbox" name="' . $id . '" value ="1" /> 
				</th></tr>';
		}
	}
	echo '<tr><td>';		
	echo '<input type="hidden" name="namefield" value="' . $namefield . '" />
		</td></tr>
		</table>';	
	echo '<p class="submit"><input type="button" name="reverseselect" onclick="checkAll(document.getElementById(\'deletefieldform\'));" value="Reverse Selection"/></p><br /><br />';	
	echo '<p class="submit"><input type="submit" name="deletefield" value="Delete Custom Field &raquo;" /></p>';
	echo '</form> 
		</div>';
	
}

/*****The Core ****/

function DC_DeleteCustom(){
	
	global $namefield;
	if ($_POST['deletefield']){
		$namefield = $_POST['namefield'];
		$n=0;
		foreach($_POST as $key => $value){
			if ($value == 1) {
				delete_post_meta ($key, $namefield);
				$n++;
			}
		}		
		if ($n==0){  //No IDS selected
			echo '<div id="message" class="updated fade"><p>Do you want to delete the Custom Field: "'. $namefield . '" from any post?</p><p><strong>Please, select a post.</strong></div>';
			DC_ShowDelete($namefield);		
		}
		else {
			echo '<div id="message" class="updated fade"><p><strong>';
			_e("Custom field deleted.");
			echo'</strong></p></div>'; 
			DC_ShowSelect();		
		}			
}

	else { 

		if ($_POST['selectfield']){

			if (($_POST['metakeyselect'])&&(($_POST['metakeyselect'])!='')){				
				$namefield = $_POST['metakeyselect'];
				DC_ShowDelete($namefield);			
			}
			else {
				echo '<div id="message" class="updated fade"><p><strong>Select a Custom Field to delete</strong></p></div>'; 	
				DC_ShowSelect();
							
			}	
		}
		else{
			DC_ShowSelect();					
		}
	}
}

function DeleteCustom_admin_menu(){
		add_management_page('Delete Custom', 'DeleteCustom', 9, basename(__FILE__), 'DC_DeleteCustom');
}

add_action('admin_menu', 'DeleteCustom_admin_menu');

?>
