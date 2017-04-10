<?php
	function star_cloudprnt_get_selected_printer_mac($selectedPrinter)
	{
		$printerList = star_cloudprnt_get_printer_list();
		$printerMac;
		foreach ($printerList as $printer)
		{
			if ($printer['name'] == $selectedPrinter)
			{
				$printerMac = $printer['printerMAC'];
				break;
			}
		}
		return $printerMac;
	}
	
	function star_cloudprnt_change_printer_name()
	{
		$selectedPrinter = base64_decode($_GET['printersettings']);
		$printerMac = star_cloudprnt_get_selected_printer_mac($selectedPrinter);

		if (isset($printerMac))
		{
			// Sanitize and escape before saving
			$newPrinterName = esc_attr(sanitize_text_field(base64_decode($_GET['npn'])));
			// Validate
			if (strlen($newPrinterName) < 3 || strlen($newPrinterName) > 35)
			{
				header('location: ?page='.$_GET['page'].'&printersettings='.base64_encode($selectedPrinter).'&errorCode=1');
				return;
			}
			// Save printer data in CloudPRNT
			$printer = new Star_CloudPRNT_Printer($printerMac);
			$printer->updatePrinterData("name", $newPrinterName);
			// Save selected printer in WordPress
			if ($selectedPrinter == get_option('printer-select')) update_option('printer-select', $newPrinterName);
			// Redirect to new printer page
			header('location: ?page='.$_GET['page'].'&printersettings='.base64_encode($newPrinterName));
		}
	}
	
	function star_cloudprnt_clear_printer_queue()
	{
		$printerMac = star_cloudprnt_get_selected_printer_mac(base64_decode($_GET['printersettings']));
		
		if (isset($printerMac))
		{
			star_cloudprnt_queue_clear_list($printerMac);
			header('location: ?page='.$_GET['page'].'&printersettings='.$_GET['printersettings']);
		}
	}
	
	function star_cloudprnt_delete_printer()
	{
		$printerMac = star_cloudprnt_get_selected_printer_mac(base64_decode($_GET['printersettings']));
		
		if (isset($printerMac))
		{
			deletePrinter($printerMac);
			header('location: ?page='.$_GET['page']);
		}
	}

	function star_cloudprnt_show_printer_settings_page()
	{
		$selectedPrinter = base64_decode($_GET['printersettings']);
		$printerList = star_cloudprnt_get_printer_list();
		
		$printerdata;
		foreach ($printerList as $printer)
		{
			if ($printer['name'] == $selectedPrinter)
			{
				$printerdata = $printer;
				break;
			}
		}
		
		?>
		
		<h2>Printer Information</h2>
			<script>
				function showDiv() {
					if (document.getElementById('editPrinterNameContainer').style.display == "block")
					{
						document.getElementById('editPrinterNameContainer').style.display = "none";
						document.getElementById('changeNameLabel').innerHTML = "Rename";
					}
					else
					{
						document.getElementById('editPrinterNameContainer').style.display = "block";
						document.getElementById('changeNameLabel').innerHTML = "Hide";
					}
				}
				function savePrinterName()
				{
					var newName = document.getElementById("printerName").value;
					window.location.href = '?page=<?php echo $_GET['page']; ?>&printersettings=<?php echo $_GET['printersettings']; ?>&npn='+btoa(newName);
				}
			</script>
			<?php
				$onlineString = '<span style="color: orange">Unknown</span>';
				if ($printerdata['printerOnline']) $onlineString = '<span style="color: green">Connected</span>';
				else $onlineString = '<span style="color: red">Not Connected</span>';
				$httpStatus = str_replace("%", " ", $printerdata['statusCode']);
				$httpStatus = str_replace(" 20", " ", $httpStatus);
				
				echo "<strong>Name:</strong> ".$printerdata['name'].' - <a id="changeNameLabel" href="javascript:void(0);" onclick=\'showDiv()\'>Rename</a><br>';
				echo '<div id="editPrinterNameContainer" style="display: none">';
					echo '<input id="printerName" type="text" name="printer" id="nprinter" placeholder="'.$printerdata['name'].'" value="'.$printerdata['name'].'" autocomplete="off">';
					echo '<a href="javascript: void(0);" onclick="savePrinterName()" style="padding-left: 10px">Save</a>';
				echo '</div>';
				if (isset($_GET['errorCode']) && $_GET['errorCode'] == 1) echo '<script type="text/javascript">showDiv()</script><span style="color:red;">Error: The new printer name must be between 3 and 35 characters long.</span><br>';
				echo "<strong>Poll Interval:</strong> ".$printerdata['GetPollInterval']."<br>";
				echo "<strong>Connectivity:</strong> ".$onlineString."<br>";
				echo "<strong>ASB Status Code:</strong> ".$printerdata['status']."<br>";
				echo "<strong>HTTP Status Code:</strong> ".$httpStatus."<br>";
				echo "<strong>Last Communication:</strong> ".date("D j M y - H:i:s", $printerdata['lastActive']);
			?>
			
			<h2>Printer Identification</h2>
			<?php
				echo "<strong>MAC Address:</strong> ".strtoupper($printerdata['printerMAC'])."<br>";
				echo "<strong>IP Address:</strong> ".$printerdata['ipAddress'];
			?>
			
			<h2>Interface</h2>
			<?php
				echo "<strong>Client Type:</strong> ".$printerdata['ClientType']."<br>";
				echo "<strong>Client Version:</strong> ".$printerdata['ClientVersion'];
			?>
			
			<h2>Supported Encodings</h2>
			<?php
				$encodings = explode(';', $printerdata['Encodings']);
				foreach ($encodings as $encoding)
				{
					echo $encoding."<br>";
				}
			?>
			
			<h2>Printer Queue</h2>
			<?php
				$queueItems = star_cloudprnt_queue_get_queue_list($printerdata['printerMAC']);
				if (empty($queueItems)) echo 'No items found in printer queue.<br>';
				else
				{
					echo '<table>';
					echo '<tr>';
						echo '<th style="padding: 5px">Priority</th>';
						echo '<th>File Name</th>';
					echo '</tr>';
						foreach ($queueItems as $queueNumber=>$item)
						{
							echo '<tr>';
								echo '<td style="text-align: center;">'.$queueNumber."</td>";
								echo '<td>'.$item."</td>";
							echo '</tr>';
						}
					echo '</table>';
					
					echo '<br><button class="button button-primary" onclick="location.href=\'?page='.$_GET['page']
							.'&printersettings='.$_GET['printersettings'].'&cq\'">Clear Queue</button>';
				}
			
			?>
			
			<h2>Delete Printer</h2>
			<?php
				if ($printerdata['printerOnline']) echo '<span style="color: red"><span class="dashicons dashicons-no"></span>You cannot delete the printer whilst it is connected</span>';
				else echo '<button class="button button-primary" onclick="location.href=\'?page='.$_GET['page']
						.'&printersettings='.$_GET['printersettings'].'&dp\'">Delete Printer</button>';
			?>
			
			<br><br><a href="?page=<?php echo $_GET['page']; ?>">Return to previous page</a>
		<?php
	}
?>