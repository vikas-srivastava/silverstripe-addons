# Addons module

## Overview

Allows logged-in members to submit modules, themes and widgets
to a public listing. Git, SVN and archives are supported upload formats.
These "addons" can be searched for by various criteria.
For Git and SVN based addons, download archives are automatically created.

Developed mainly to drive silverstripe.org/modules.
Not intended to be used elsewhere without extensive modification,
its more of an attempt to open up silverstripe.org development
to the wider community.

## Requirements

 * legacydatetimefields
 * gitcachedarchiver
 * subversion
 * messagequeue
 * forum
 * tagfield
 * urlfield

## Installation

	git clone git://github.com/silverstripe-labs/silverstripe-subversion.git subversion
	git clone git://github.com/candidasa/gitcachedarchiver.git gitcachedarchiver
	git clone git://github.com/silverstripe-labs/silverstripe-messagequeue.git messagequeue
	git clone git://github.com/silverstripe-labs/silverstripe-legacydatetimefields.git legacydatetimefields
	git clone git://github.com/silverstripe/silverstripe-forum.git forum
	git clone git://github.com/chillu/silverstripe-tagfield.git tagfield
	git clone git://github.com/chillu/silverstripe-urlfield.git urlfield