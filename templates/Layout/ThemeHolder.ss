<div id="themes_holder" class="typography">
	<% include TopBar %>
	
	<div id="themes_container">
		<h2><% if TitleImage %><img src="$TitleImage.URL" alt="<% if TitleImageAlt %>$TitleImageAlt<% else %>$Title<% end_if %>"><% else %>$Title<% end_if %></h2>
		
		<div id="left_content">
			<h3 id="themes_tagline">
				Give your site a new look with a SilverStripe theme. All themes are created by members of 
				our Open Source Community and are completely free to use. 
			</h3>
			
			<form id="sort_options_form" name="sort_options_form">
				<p>Sort Themes: <br/>
				<select name="sort" onchange="if(this.value) document.sort_options_form.submit();">
					<option value="added" <% if SortOption=added %>selected<% end_if %>>By Most Recently Added</option>
					<option value="name" <% if SortOption=name %>selected<% end_if %>>By Name (A-Z)</option>
					<option value="creator" <% if SortOption=creator %>selected<% end_if %>>By Maintainer (A-Z)</option>
				</select>
				</p>
			</form>
			
			<ul id="theme_list">
			<% control Themes %>
				<li>
					<h3 class="itemTitle"><a href="$Link">$Title</a></h3>
						<p class="themePreview">
						<% if ScreenshotImage %>
							<a href="$Link" class="no_border"><% control ScreenshotImage %>$CroppedImage(170,120)<% end_control %></a>
						<% else %>
							<a href="$Link" class="no_border"><img src="$ThemeDir/images/comingsooon.png" alt="Coming soon!"></a>
						<% end_if %>
					</p>

					<p class="maintainers"><span>$Created.Long<br>Maintainer(s):</span>
						<% if Maintainers %>
							<% control Maintainers %>
								<a href="$Link">$Nickname</a><% if Last %><% else %>, <% end_if %>
							<% end_control %>
						<% else %>
							Unmaintained!
						<% end_if %>
					</p>
					
					<p class="theme_action_containter">
						<a href="$DownloadFile.URL">Download</a><br />
						<a href="$Link">More info</a>
					</p>
				</li>
				<% if Column = 3 %> 
					<li class="clear seperator"></li>
				<% end_if %>
			<% end_control %>
			</ul>
			
			<!-- PAGINATION -->
			<% if Themes.MoreThanOnePage %>
			<div id="PageNumbers" class="boxPagination">
				<p>
					<span id="pagination_numbers">
				    	<% control Themes.PaginationSummary(3) %>
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
					
					<% if Themes.NotFirstPage %>
						<a class="prev" href="$Themes.PrevLink" title="View the previous page">Prev</a>
					<% end_if %>
					
					<% if Themes.NotLastPage %>
						<a class="next" href="$Themes.NextLink" title="View the next page">Next</a>
					<% end_if %>
				</p>
			</div>
			<% end_if %>	
		</div> <!-- #left_content -->
		<div id="right_content">
			<h3>Make your own theme</h3>
			<p class="make_theme">
				Want to create your own theme? Read our <a href="http://doc.silverstripe.com/doku.php?id=themes:developing">Guide to Making your own Theme</a>. Once you've created and debugged your Theme, submit it to this directory. 
			</p>
			<p><a class="submitButton" href="{$Link}manage/add">Submit your Theme</a></p>
		</div>	
	</div> <!-- #theme_container -->
	<p id="contribution_thanks_msg">
		Thanks to all those who have volunteered their time to make modules, widgets and themes
	</p>
</div>