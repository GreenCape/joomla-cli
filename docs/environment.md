## Test Environments

Using the `Environment` tab, you can define the test environments.

Although it seems to be very important to test as much as possible, there are way too many combinations of the selections shown in the images above.
2 Joomla! versions combined with 2 databases, 3 web servers and 5 PHP versions define 60 different test environments. Adding one more Joomla version would increase the number to 90(!).

Fortunately, this problem is well known, and there is a solution for it, called [all-pairs testing](https://en.wikipedia.org/wiki/All-pairs_testing).
This method uses the smallest set of combinations, that contains all pairs from the four dimensions 'Joomla', 'Database', Web Server' and 'PHP'. 

```xml
<environment name="env-name">
    <joomla version="3" sampleData="data"/>
    <database driver="mysql" name="joomla3" prefix="j3m_"/>
    <server type="nginx" offset="UTC"/>
    <cache enabled="0" time="15" handler="file"/>
    <debug system="1" language="1"/>
    <meta description="Test installation" keywords="" showVersion="0" showTitle="1" showAuthor="1"/>
    <sef enabled="0" rewrite="0" suffix="0" unicode="0"/>
    <feeds limit="10" email="author"/>
    <session lifetime="15" handler="database"/>
</environment>
```
