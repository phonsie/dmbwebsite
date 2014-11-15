<?php

	define('INCLUDE_CHECK',true);

	require '../dmbConfig/config.php';
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("How-To");

	echo "<h1><center>How-To</center></h1>";
	
?>
		<br>Download and install the <a href="DMBConcertCatalogue.zip">program</a>, unzip it and click on the setup.exe. Follow the prompts.</br>
		<br>The program should appear in your "All Programs" menu under the a folder name "DMB Concert Catalogue".</br>
		<br>Run the program (DMB Concert Catalogue).</br>
		<br>Paste your "Secret" (which you can get from the top menu bar above, assuming you've registered and logged in) into the field at the top right of the program.</br>
		<br>Browse to where your sources are stored using the "Select Folder" button on the bottom left.</br>
		<br>To begin with just click on a folder which contains a few sources.</br>
		<br>Click the "Search" button.</br>
		<br>You can see the progress in the top window, errors will be reported in the lower window. Typical errors which appear are either because the source you have doesn't have recognised md5 checksums from etree or the tracks have already been added.</br>
		<br>After all the sources have been checked a message will appear in the top window reporting where the import file has been written to.</br>
		<br>Upload that file <a href="import.php">here</a>.</br>
		<br>After about 5 minutes you should see the shows in the list of <a href="import.php">recently imported shows.</a></br>
		<br></br>			
		
		<i>THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS AS IS AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
		FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
		(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
		WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</i>

<?php		
	setFooter();
?>