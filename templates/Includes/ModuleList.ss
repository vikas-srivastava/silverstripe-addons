<% control Modules %>
	<% if Submitted %>
		<% if ModulePages %>
			<h2>Your search returned the following results:</h2>
		<% else %>
			<p> Sorry, there are no results match your search criteria. </p>
		<% end_if %>
	<% end_if %>
	
	<% if ModulePages %>
		<ul id="module_list">
			<% control ModulePages %>
				<li>
					<div class="moduleListLeft">
						<h3><a href="$Link">$Title</a> 
							<% if CurrentRelease %>
								<% control CurrentRelease %>
									<% if ReleaseVersion = Unreleased %>
										<span>[Unreleased]</span>
									<% else %>    	        
										<span>[v$ReleaseVersion]</span>
									<% end_if %>
								<% end_control %>
							<% end_if %>
						</h3>
						<div class="moduleImage">
							<a href="$Link" class="hasimg">
							<% if ScreenshotImage %>
								<% control ScreenshotImage.SetWidth(200) %><img src="$URL" alt="Screenshot" /><% end_control %>
							<% else %>
								<img src="$ThemeDir/images/comingsooon.png" alt="Coming soon!" />
							<% end_if %>
							</a>
							<div class="overlay maintainer$Maintainer"></div>
						</div>
					</div>

					<div class="moduleListRight">
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
								<span>Supported By:</span> Not Supported
							<% else %>
								<span>Supported By:</span> $Maintainer
							<% end_if %>					
						</p>
						<p class="moduleActions">
							<% if URLToDemo %><a href="$URLToDemo">See this Module in action</a><% end_if %>
							<% if SupportForum %>
								<a href="$SupportForum.Link">Visit the Support Forum</a>
							<% else %>
								<a href="$AlternativeSupportForum.Link">Visit the Support Forum</a>
							<% end_if %>
						</p>
						<div class="abstract">$Abstract</div>
						<p class="moduleReleases">
							<% control AddonReleases %>
								<% if LiteralCompatibleSilverStripeVersions %>
								Release
									<% if ReleaseVersion = Unreleased %>
										[Unreleased]
									<% else %>    	        
										[v$ReleaseVersion]
									<% end_if %>
									compatible with SilverStripe $LiteralCompatibleSilverStripeVersions
								<% end_if %>
							<% end_control %>
						</p>
						<p>
							<% if Download %><a class="downloadLink" href="$Download.URL">Download</a> <% end_if %>
							<a class="moreInfoLink" href="$Link">More info</a>
						</p>
					</div>
				</li>
			<% end_control %>
		</ul>
	<% end_if %>
<% end_control %>