<button class="open-button" style="background-color: #702283;" onclick="openForm()"><b>Logs</b></button>
<div class="chat-popup" id="myForm">
  <form class="form-container">
  		<div style="overflow-y:scroll; max-height: 300px; max-width: 400px;">
    		<label for="msg"><h3>logs made by the system : </h3>
    			<?php 
    			if(isset($_SESSION['logs'])){
    				echo $_SESSION['logs'];
    			}
    			?>
    		</label>
  		</div>
    <button type="button" class="btn cancel" onclick="closeForm()">Close</button>
  </form>
</div>	