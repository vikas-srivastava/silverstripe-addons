<p>
	<strong>Version:</strong>
		<% if ReleaseVersion = Unreleased %>
			<span>[Unreleased]</span>
		<% else %>    	        
			<span>[v$ReleaseVersion]</span>
		<% end_if %>
	<br />
	<strong>Date:</strong> $ReleaseDate<br />
	<strong>Compatible with:</strong> SilverStripe $LiteralCompatibleSilverStripeVersions<br />
	<strong>Download:</strong> 
	<% if Download %>
		<%-- GA doesn't track download links, add manually --%>
		<a href="$Download.URL" onclick="javascript: _gaq.push(['_trackPageview', '$Download.URL']);">$Download.Name</a>
	<% else %>
		No Download Available
	<% end_if %>
	<br />
</p>
<% if VersionControlChoice == "Subversion" %><p><strong>Subversion access:</strong> <code title="$SubversionURL">$SubversionURL</code></p><% end_if %>
<% if VersionControlChoice == "Git" %><p><strong>Git access:</strong>
	<% if GitURL %><br/>Repository: <code title="$GitURL">$GitURL</code><% end_if %>
	<% if GitBranch %><br/>Branch: <code title="$GitBranch">$GitBranch</code><% end_if %>
	<% if GitTag %><br/>Tag: <code title="$GitTag">$GitTag</code><% end_if %></p>
<% end_if %>