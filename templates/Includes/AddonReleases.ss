<div id="module_page_right_content">
	<h2>Release(s)</h2>
	<ul>
		<% if CurrentRelease %>
		<li class="release latestRelease">
			<h3>Latest release</h3>
			<% control CurrentRelease %>
				<% include AddonRelease %>
			<% end_control %>
		</li>
		<% end_if %>
	
		<% if OlderReleases %>
			<li class="release">
				<h3>Older release(s)</h3>
				<% control OlderReleases %>
					<% include AddonRelease %>
				<% end_control %>
			</li>
		<% end_if %>

		<% if MasterDownload %>
			<li class="release">
				<h3>Latest master build</h3>
				<p>
					To get a preview of our next release, download the latest build of unstable git master branch here.  Please
					be careful: this is more likely to contain bugs, especially on modules undergoing a lot of development.<br /><br />
					<% control MasterDownload %>
						<strong>SHA hash:</strong> $currentRev<br />
						<strong>Build Date:</strong> $currentDate<br />
					<strong>Download:</strong> <a href="$URL" onclick="javascript: _gaq.push(['_trackPageview', '$URL']);">$Name</a><br />
					<% end_control %>
				</p>
				<p><strong>Unstable Git access:</strong> <code title="$GitRepo">$GitRepo</code></p>
			</li>
		<% else %>
			<% if TrunkDownload %>
				<li class="release">
					<h3>Latest trunk build</h3>
					<p>
						To get a preview of our next release, download the latest build of unstable trunk here.  Please
						be careful: this is more likely to contain bugs, especially on modules undergoing a lot of development.<br /><br />
						<% control TrunkDownload %>
							<strong>Revision:</strong> #$currentRev<br />
							<strong>Build Date:</strong> $currentDate<br />
							<strong>Download:</strong> <a href="$URL" onclick="javascript: _gaq.push(['_trackPageview', '$URL']);">$Name</a><br />
						<% end_control %>
					</p>
					<p><strong>Unstable Subversion access:</strong> <code title="$SubversionTrunk">$SubversionTrunk</code></p>
				</li>
			<% end_if %>
		<% end_if %>
	</ul>
</div>