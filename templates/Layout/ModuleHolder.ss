<div id="module-holder" class="typography">
	<% include TopBar %>
	
	<div id="module_container">
		<h2><img src="$ThemeDir/images/extending/modules_header.gif" alt="Modules"></h2>

		<div id="left_content">
			
			<h3 id="modules_tagline">
				Modules can extend SilverStripe in many ways. From blogs to complex
				publishing on a static server, our free modules will make your site sing.
			</h3>
			<form id="sort_options_form" name="sort_options_form">
				$KeyWordSearchField
				<div id="AdvancedSearchOptions">
					<div class="popupInfo">$SupportLevelField
						<span class="hoverPopup">$SupportedByExplanation</span>
						<a class="hoverPopupLink">What does this mean?</a>
					</div>
					<div>$SSVersionsField</div>
					<div>
						<label for="SortModules">Sort Modules:</label>
						<select id="SortModules" name="sort">
							<option value="name" <% if SortOption=name %>selected<% end_if %>>By Name (A-Z)</option>
							<option value="added" <% if SortOption=added %>selected<% end_if %>>By Most Recently Added</option>
							<option value="creator" <% if SortOption=creator %>selected<% end_if %>>By Maintainer (A-Z)</option>
							<option value="minSSversion" <% if SortOption=minSSversion %>selected<% end_if %>>By Minimum SilverStripe Version Requirement</option>
							<option value="supportlevel" <% if SortOption=supportlevel %>selected<% end_if %>>By Module Support Level</option>
						</select>
					</div>
				</div>
				<a class="active_sumbit" href="#">Search</a>
			</form>
			<!--<p>
				<% control ThirdMenu %>
					<% if IsCurrent %>
						<strong>$Title</strong>
					<% else %>
						<a href="$Link">$Title</a>
					<% end_if %>&nbsp; &nbsp;
				<% end_control %>
			</p>-->
			
			<% include ModuleList %>
			
			<% control Modules %>
				<% if ModulePages.MoreThanOnePage %>
				<div id="PageNumbers" class="boxPagination">
					<p>
						<% if ModulePages.NotFirstPage %>
							<a class="prev" href="$ModulePages.PrevLink" title="View the previous page">Prev</a>
						<% end_if %>
				
						<span id="pagination_numbers">
					    	<% control ModulePages.PaginationSummary(4) %>
								<% if CurrentBool %>
									$PageNum
								<% else %>
									<% if PageNum = --- %>
										...
									<% else %>
										<a href="$Link" title="View page number $PageNum">$PageNum</a>
									<% end_if %>
								<% end_if %>
							<% end_control %>
						</span>
				
						<% if ModulePages.NotLastPage %>
							<a class="next" href="$ModulePages.NextLink" title="View the next page">Next</a>
						<% end_if %>
					</p>
				</div>
				<% end_if %>
			<% end_control %>
			
		</div> <!-- #left_content -->
		<div id="right_content">
		
			<h3>Make your own module</h3>
			<img src="$ThemeDir/images/extending/save_time_with_modules.jpg" alt="Save time with modules image">
		
			<p>
				Want to create your own module? Read our <a href="http://doc.silverstripe.com/doku.php?id=creating-modules">Guide to Making your own Module</a>.
				Once you've created and debugged your module, submit it to our directory.
			</p>
			<p><a class="submitButton" href="{$Link}manage/add">Submit your Module</a></p>
		
			$RightContent
		
		</div>
	</div> <!-- #module_container -->
	<p id="contribution_thanks_msg">
		Thanks to all those who have volunteered their time to make modules, widgets and themes
	</p>
</div>
