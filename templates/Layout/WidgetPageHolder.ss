<div id="widgets-holder" class="typography">
	<% include TopBar %>
	
	<div id="widgets_container">
		<h2><% if TitleImage %><img src="$TitleImage.URL" alt="<% if TitleImageAlt %>$TitleImageAlt<% else %>$Title<% end_if %>"><% else %>$Title<% end_if %></h2>

		<div id="left_content">
			<h3 id="widgets_tagline">
				Watch our <a class="fancy" href="http://silverstripe.com/assets/screencasts/SilverStripe-Blog-DragDrop-Widgets.swf">drag-and-drop widgets demo</a> and see how easy it is to get bite-size features in your sidebar.
				<p>
					<span>Got questions? Ask them on the</span> <a title="Go to Widgets forum" href="widgets-2/">Widgets forum</a>.
				</p>
			</h3>
			
			<form id="sort_options_form" name="sort_options_form">
				<p>Sort Widgets: <br/>
				<select name="sort" onchange="if(this.value) document.sort_options_form.submit();">
					<option value="added" <% if SortOption=added %>selected<% end_if %>>By Most Recently Added</option>
					<option value="name" <% if SortOption=name %>selected<% end_if %>>By Name (A-Z)</option>
					<option value="creator" <% if SortOption=creator %>selected<% end_if %>>By Maintainer (A-Z)</option>
				</select>
				</p>
			</form>
			
			<ul id="widgets_list">
			<% control Widgets %>
				<li class="widgetListItem">
					<h3 class="itemTitle"><a href="$Link">$Title</a> <span>[v$CurrentVersion]</h3>
					<a id="widget_download" class="whiteDownloadLink" href="$DownloadFile.URL">Download</a>

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
				</li>
			<% end_control %>
			</ul>
			<div class="clear"></div>
			<!-- PAGINATION -->
			<% if Widgets.MoreThanOnePage %>
			<div id="PageNumbers" class="boxPagination">
				<p>
					<span id="pagination_numbers">
				    	<% control Widgets.PaginationSummary(3) %>
							<% if CurrentBool %>
								<span>$PageNum</span>
							<% else %>
								<% if Link %>
									<a href="$Link" title="View page number $PageNum">$PageNum</a>
								<% else %>
									&hellip;
								<% end_if %>
							<% end_if %>
						<% end_control %>
					</span>
				
					<% if Widgets.NotFirstPage %>
						<a class="prev" href="$Widgets.PrevLink" title="View the previous page">Prev</a>
					<% end_if %>
					
					<% if Widgets.NotLastPage %>
						<a class="next" href="$Widgets.NextLink" title="View the next page">Next</a>
					<% end_if %>
				</p>
			</div>
			<% end_if %>	
		</div>
		
		<div id="right_content">
			<h3>Make your own widget</h3>
			<p class="make_widget">
				They're easier to create than modules. Start making your own widgets by reading our <a class="Guide to making your own widgets." href="http://doc.silverstripe.com/doku.php?id=widgets#writing_your_own_widgets#">Guide to making your own widgets.</a> Once you've created and checked your widget, submit it to this directory.
			</p>
			<p><a class="submitButton" href="{$Link}manage/add">Submit your Widget</a></p>
		</div>

	</div> <!-- #widgets_container -->
</div>