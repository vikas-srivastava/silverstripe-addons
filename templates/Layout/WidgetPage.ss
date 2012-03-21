<div id="widgets-holder" class="typography">
	<% include TopBar %>

	<div id="widgets_container">
		<div id="theme_nav">
			<a id="themes_index_link" href="widgets">Widgets Index</a>
		</div>
	
		<div id="left_content">
		
			<div id="widgets_list">
				<div class="widgetListItem">
					<h3 class="itemTitle">$Title <span>[v$CurrentVersion]</h3>
					<a id="widget_download" class="whiteDownloadLink" href="$DownloadFile.URL" onclick="javascript: _gaq.push(['_trackPageview', '$DownloadFile.URL']);">Download</a>
			
					<p class="maintainers"><span>Maintainer(s):</span>
						<% if Maintainers %>
							<% control Maintainers %>
								<a href="$Link">$Nickname</a><% if Last %><% else %>, <% end_if %>
							<% end_control %>
						<% else %>
							Unmaintained!
						<% end_if %>						
					</p>
					
					<% if CanEdit %><p><a class="editLink" href="$EditLink">edit this widget</a></p><% end_if %>
										
					<ul>
						<li>$Content</li>
						<li>Released: $ReleaseDate</li>
					</ul>
					
					<% if CMSImage %>
					<div class="widgetCMSScreenshot">
						<h4>CMS View</h4>
						<% control CMSImage.SetWidth(200) %><img src="$URL" alt="Screenshot"><% end_control %>
					</div>
					<% end_if %>
					
					<% if ScreenshotImage %>
					<div class="widgetScreenshot">
						<h4>Site View</h4>
						<% control ScreenshotImage.SetWidth(200) %><img src="$URL" alt="Screenshot"><% end_control %>
					</div>
					<% end_if %>
					
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>