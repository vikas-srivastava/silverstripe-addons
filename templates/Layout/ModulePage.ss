<div id="module_page" class="typography">
	<% include TopBar %>

	<div id="module_container" class="individualModule">
		<div id="module_actions">
			<a id="module_index_button" class="moduleActionButton" href="$Parent.Link">Module index</a>
		</div>

		<h2>
			$Title
			<span>$ReleaseVersion</span>
			<% if CurrentRelease %>
				<% control CurrentRelease %>
					<% if ReleaseVersion = Unreleased %>
						<span>[Unreleased]</span>
					<% else %>    	        
						<span>[v$ReleaseVersion]</span>
					<% end_if %>
				<% end_control %>
			<% end_if %>
		</h2>
	
		<p class="maintainers"><span>Maintainer(s):</span>
		<% if Maintainers %>
			<% control Maintainers %>
				<a href="$Link">$Nickname</a><% if Last %><% else %>, <% end_if %>
			<% end_control %>
		<% else %>
			Unmaintained!
		<% end_if %>
		<br />
		<% if Maintainer=None %>
			<span>Supported by:</span> Not supported
		<% else %>
			<span>Supported by:</span> $Maintainer
		<% end_if %>
		<% if CanEdit %><br><a class="editLink" href="$EditLink">edit this module</a><% end_if %>
		</p>
	
		<p id="module_links">
			<% if URLToDemo %><a class="demoLink" href="$URLToDemo">See this module in action</a><% end_if %>
			<% if SupportForum %>
				<a class="forumLink" href="$SupportForum.Link">Visit the support forum</a>
			<% else %>
				<a class="forumLink" href="$AlternativeSupportForum.Link">Visit the support forum</a>
			<% end_if %>
			<% if FileTicketLink %><a class="fileticketLink" href="$FileTicketLink">File a bug ticket</a><% end_if %>
			<% if ExistingTicketsLink %><a class="existingticketsLink" href="$ExistingTicketsLink">View existing bug tickets</a><% end_if %>
			<% if Download %><a class="downloadLink" href="$Download.URL">Download</a><% end_if %>
		</p>
	

		
		<div id="module_content">
			<div id="module_page_left_content">
				$Abstract
				$Content
				<% if ScreenshotImage %>
					<% if ScreenshotImageWidth %>
						<% control ScreenshotImage.SetWidth(520) %>
							<div class="moduleDetailImage">
								<img src="$URL" alt="Module screenshot">
								<div class="overlay maintainer$Maintainer"></div>
							</div>
						<% end_control %>
					<% else %>
						<% control ScreenshotImage %>
							<div class="moduleDetailImage">
								<img src="$URL" alt="Module screenshot">
								<div class="overlay maintainer$Maintainer"></div>
							</div>
						<% end_control %>
					<% end_if %>
				<% end_if %>
			</div>
			
			<% include AddonReleases %>
			
			<div class="spacer">&nbsp;</div>
			
			<ul id="module_footer">
				<% if InstallationLink %>
				<li id="module_footer_installation">
					<h3 class="fixedHeight"><a href="$InstallationLink">Installation steps</a></h3>
					<p>Learn how to install the $Title module with our step-by-step guide.</p>
				</li>
				<% end_if %>
				<% if FAQLink %>
				<li id="module_footer_faq">
					<h3 class="fixedHeight"><a href="$FAQLink">$Title FAQs</a></h3>
					<p>Read the most commonly asked questions.</p>
				</li>
				<% end_if %>
				<% if GettingStartedLink %>
				<li id="module_footer_gettingstarted">
					<h3 class="fixedHeight"><a href="$GettingStartedLink">Getting started...</a></h3>
					<p>Learn how others got started with the $Title module.</p>
				</li>
				<% end_if %>
				
				<li id="module_footer_forum">
					<h3 class="fixedHeight"><a href="<% if SupportForum %>$SupportForum.Link<% else %>$AlternativeSupportForum.Link<% end_if %>">Still got questions?</a></h3>
					<p>Ask them over on the discussion forum.</p>
				</li>

				<% if BugTrackerLink %>
					<% if Maintainer=SilverStripe %>
					<% else %>
						<li id="module_footer_bagtracker">
							<h3 class="fixedHeight"><a href="$BugTrackerLink">Found a bug?</a></h3>
							<p>Report it in the bug tracker for the module.</p>
						</il>
					<% end_if %>
				<% end_if %>
			</ul>
			
		</div>
	
		<div class="spacer">&nbsp;</div>

	</div>
</div>
