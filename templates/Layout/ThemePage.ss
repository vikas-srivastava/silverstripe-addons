<div id="themes_holder" class="typography">
	<% include TopBar %>
	
	<div id="theme_container">
		<div id="theme_nav">
			<a id="themes_index_link" href="themes">Themes Index</a>
		</div>
		<div id="theme_sidebar">
			<h2 id="theme_name">$Title</h2>
			<h4 id="theme_creator">
                Maintainer(s):
        		<% if Maintainers %>
        			<% control Maintainers %>
        				<a href="$Link">$Nickname</a><% if Last %><% else %>, <% end_if %>
        			<% end_control %>
        		<% else %>
        			Unmaintained!
        		<% end_if %>

				<% if CanEdit %><br><a class="editLink" href="$EditLink">edit this theme</a><% end_if %>
			</h4>
			<div id="theme_note_container">
				<h4 class="theme_header">Notes:</h4>
				<div id="theme_note">
					$Content
				</div>
			</div>
		</div>
		
		<div id="content">
			<div id="theme_actions">
				<div id="theme_text_links">
				<a href="themes-2">Visit the Themes Forum</a>
				</div>
				
				<a id="theme_download" class="whiteDownloadLink" href="$DownloadFile.URL" onclick="javascript: _gaq.push(['_trackPageview', '$DownloadFile.URL']);">
					Download
				</a>
			</div>
			
			<% control ScreenshotImage.SetWidth(520) %>
				<img src="$URL" alt="Screenshot">
			<% end_control %>
			
			<div id="theme_info">
				<h4 class="theme_header">Theme info:</h4>
				<ul id="theme_info_list">
					<li>Released: <span>$ReleaseDate.Format(d M y)</span></li>
					<li>Version: <span>$CurrentVersion</span></li>
					<li>Code name: $ShortName (folder name for theme)</li>
				</ul>
			</div>
		</div>
		<div class="clear"></div>
	</div> <!-- end of theme_container -->
	
	<div id="installation_steps">
		<h3>Installation Steps</h3>
		<ol id="installation_steps_list">
			<li>
				1. After unzipping your theme download, put the contents of the zip file into your /themes directory in your silverstripe project. 
			</li>
			<li>
				2. To get your theme up and running, you’ll need to change one line of code in the _config.php file. You’ll find the config file in: ./mysite/_config.php:
			</li>
			<li>
				2. Add the following line of code:
				<code>SSViewer::set_theme('themename');</code>
			</li>
			<li>
				4. Once you’ve added the code, visit your homepage, and flush the cache (append ?flush=1 to the url). Your new theme should now be working.
			</li>
		</ol>
	</div>
</div>