#Splitdown
## A Ghost like Markdown Editor replacement for Wordpress

### How to install
1. Clone the plugin to your wp-contents/plugins directory.
2. You need to run ```git submodule init``` and ```git submodule update``` to pull showdown.js and html2markdown
3. In your WP Dashboard go to ```Settings``` and than ```Writing```, scroll down to the Splitdown section and select on which post types you wish to use Splitdown on.
4. Enjoy

### Note:
I implemented an experimental version of the WordPress Media Manager. At the moment it does not work in distraction free mode.
Since Markdown doesn't support css classes for elements, I need to write a showdown extension to fix this, so images can be displayed properly.
As always this may take some time to implement, because I work alone in my spare time on this :) If you want to help, just
fork this project and submit a pull request for your changes.

**If you find a bug or have a suggestion leave a ticket at https://github.com/Necrotex/Splitdown/issues.**

### Javascript libraries used:
+ [showdown](https://github.com/coreyti/showdown)
+ [html2mkardown](https://github.com/kates/html2markdown)