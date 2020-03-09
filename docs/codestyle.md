# PSR12 Coding Standard
## Operator Spacing
All binary and ternary (but not unary) operators MUST be preceded and followed by at least one space. This includes all arithmetic, comparison, assignment, bitwise, logical (excluding ! which is unary), string concatenation, type operators, trait operators (insteadof and as), and the single pipe operator (e.g. ExceptionType1 | ExceptionType2 $e).
  <table>
   <tr>
    <th>Valid: At least 1 space used.</th>
    <th>Invalid: No spacing used.</th>
   </tr>
   <tr>
<td>

    if ($a === $b) {
        $foo = $bar ?? $a ?? $b;
    } elseif ($a > $b) {
        $variable = $foo ? 'foo' : 'bar';
    }

</td>
<td>

    if ($a===$b) {
        $foo=$bar??$a??$b;
    } elseif ($a>$b) {
        $variable=$foo?'foo':'bar';
    }

</td>
   </tr>
  </table>
## Class Instantiation
When instantiating a new class, parenthesis MUST always be present even when there are no arguments passed to the constructor.
  <table>
   <tr>
    <th>Valid: Parenthesis used.</th>
    <th>Invalid: Parenthesis not used.</th>
   </tr>
   <tr>
<td>

    new Foo();

</td>
<td>

    new Foo;

</td>
   </tr>
  </table>
## Short Form Type Keywords
Short form of type keywords MUST be used i.e. bool instead of boolean, int instead of integer etc.
  <table>
   <tr>
    <th>Valid: Short form type used.</th>
    <th>Invalid: Long form type type used.</th>
   </tr>
   <tr>
<td>

    $foo = (bool) $isValid;

</td>
<td>

    $foo = (boolean) $isValid;

</td>
   </tr>
  </table>
## Compound Namespace Depth
Compound namespaces with a depth of more than two MUST NOT be used.
  <table>
   <tr>
    <th>Valid: Max depth of 2.</th>
    <th>Invalid: Max depth of 3.</th>
   </tr>
   <tr>
<td>

    use Vendor\Package\SomeNamespace\{
        SubnamespaceOne\ClassA,
        SubnamespaceOne\ClassB,
        SubnamespaceTwo\ClassY,
        ClassZ,
    };

</td>
<td>

    use Vendor\Package\SomeNamespace\{
        SubnamespaceOne\AnotherNamespace\ClassA,
        SubnamespaceOne\ClassB,
        ClassZ,
    };

</td>
   </tr>
  </table>
## Nullable Type Declarations Functions
In nullable type declarations there MUST NOT be a space between the question mark and the type.
  <table>
   <tr>
    <th>Valid: no whitespace used.</th>
    <th>Invalid: superfluous whitespace used.</th>
   </tr>
   <tr>
<td>

    public function functionName(
        ?string $arg1,
        ?int $arg2
    ): ?string {
    }

</td>
<td>

    public function functionName(
        ? string $arg1,
        ? int $arg2
    ): ? string {
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: no unexpected characters.</th>
    <th>Invalid: unexpected characters used.</th>
   </tr>
   <tr>
<td>

    public function foo(?int $arg): ?string
    {
    }

</td>
<td>

    public function bar(? /* comment */ int $arg): ?
        // nullable for a reason
        string
    {
    }

</td>
   </tr>
  </table>
## Method Name
Method names MUST be declared in camelCase.
  <table>
   <tr>
    <th>Valid: method name in camelCase.</th>
    <th>Invalid: method name not in camelCase.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        private function doBar()
        {
        }
    }

</td>
<td>

    class Foo
    {
        private function do_bar()
        {
        }
    }

</td>
   </tr>
  </table>
## Class Declaration
Each class must be in a file by itself and must be under a namespace (a top-level vendor name).
  <table>
   <tr>
    <th>Valid: One class in a file.</th>
    <th>Invalid: Multiple classes in a single file.</th>
   </tr>
   <tr>
<td>

    <?php
    namespace Foo;
    
    class Bar {
    }

</td>
<td>

    <?php
    namespace Foo;
    
    class Bar {
    }
    
    class Baz {
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: A vendor-level namespace is used.</th>
    <th>Invalid: No namespace used in file.</th>
   </tr>
   <tr>
<td>

    <?php
    namespace Foo;
    
    class Bar {
    }

</td>
<td>

    <?php
    class Bar {
    }

</td>
   </tr>
  </table>
## Side Effects
A php file should either contain declarations with no side effects, or should just have logic (including side effects) with no declarations.
  <table>
   <tr>
    <th>Valid: A class defined in a file by itself.</th>
    <th>Invalid: A class defined in a file with other code.</th>
   </tr>
   <tr>
<td>

    <?php
    class Foo
    {
    }

</td>
<td>

    <?php
    class Foo
    {
    }
    
    echo "Class Foo loaded."

</td>
   </tr>
  </table>
## Alternative PHP Code Tags
Always use &lt;?php ?&gt; to delimit PHP code, do not use the ASP &lt;% %&gt; style tags nor the &lt;script language=&quot;php&quot;&gt;&lt;/script&gt; tags. This is the most portable way to include PHP code on differing operating systems and setups.
## PHP Code Tags
Always use &lt;?php ?&gt; to delimit PHP code, not the &lt;? ?&gt; shorthand. This is the most portable way to include PHP code on differing operating systems and setups.
## Byte Order Marks
Byte Order Marks that may corrupt your application should not be used.  These include 0xefbbbf (UTF-8), 0xfeff (UTF-16 BE) and 0xfffe (UTF-16 LE).
## Constant Names
Constants should always be all-uppercase, with underscores to separate words.
  <table>
   <tr>
    <th>Valid: all uppercase</th>
    <th>Invalid: mixed case</th>
   </tr>
   <tr>
<td>

    define('FOO_CONSTANT', 'foo');
    
    class FooClass
    {
        const FOO_CONSTANT = 'foo';
    }

</td>
<td>

    define('Foo_Constant', 'foo');
    
    class FooClass
    {
        const foo_constant = 'foo';
    }

</td>
   </tr>
  </table>
## Line Endings
Unix-style line endings are preferred (&quot;\n&quot; instead of &quot;\r\n&quot;).
## End File Newline
PHP Files should end with exactly one newline.
## Line Length
It is recommended to keep lines at approximately 80 characters long for better code readability.
## Multiple Statements On a Single Line
Multiple statements are not allowed on a single line.
  <table>
   <tr>
    <th>Valid: Two statements are spread out on two separate lines.</th>
    <th>Invalid: Two statements are combined onto one line.</th>
   </tr>
   <tr>
<td>

    $foo = 1;
    $bar = 2;

</td>
<td>

    $foo = 1; $bar = 2;

</td>
   </tr>
  </table>
## Scope Indentation
Indentation for control structures, classes, and functions should be 4 spaces per level.
  <table>
   <tr>
    <th>Valid: 4 spaces are used to indent a control structure.</th>
    <th>Invalid: 8 spaces are used to indent a control structure.</th>
   </tr>
   <tr>
<td>

    if ($test) {
        $var = 1;
    }

</td>
<td>

    if ($test) {
            $var = 1;
    }

</td>
   </tr>
  </table>
## No Tab Indentation
Spaces should be used for indentation instead of tabs.
## Lowercase Keywords
All PHP keywords should be lowercase.
  <table>
   <tr>
    <th>Valid: Lowercase array keyword used.</th>
    <th>Invalid: Non-lowercase array keyword used.</th>
   </tr>
   <tr>
<td>

    $foo = array();

</td>
<td>

    $foo = Array();

</td>
   </tr>
  </table>
## Lowercase PHP Constants
The *true*, *false* and *null* constants must always be lowercase.
  <table>
   <tr>
    <th>Valid: lowercase constants</th>
    <th>Invalid: uppercase constants</th>
   </tr>
   <tr>
<td>

    if ($var === false || $var === null) {
        $var = true;
    }

</td>
<td>

    if ($var === FALSE || $var === NULL) {
        $var = TRUE;
    }

</td>
   </tr>
  </table>
## Lowercase PHP Types
All PHP types used for parameter type and return type declarations should be lowercase.
  <table>
   <tr>
    <th>Valid: Lowercase type declarations used.</th>
    <th>Invalid: Non-lowercase type declarations used.</th>
   </tr>
   <tr>
<td>

    function myFunction(int $foo) : string {
    }

</td>
<td>

    function myFunction(Int $foo) : STRING {
    }

</td>
   </tr>
  </table>
All PHP types used for type casting should be lowercase.
  <table>
   <tr>
    <th>Valid: Lowercase type used.</th>
    <th>Invalid: Non-lowercase type used.</th>
   </tr>
   <tr>
<td>

    $foo = (bool) $isValid;

</td>
<td>

    $foo = (BOOL) $isValid;

</td>
   </tr>
  </table>
## Class Declarations
There should be exactly 1 space between the abstract or final keyword and the class keyword and between the class keyword and the class name.  The extends and implements keywords, if present, must be on the same line as the class name.  When interfaces implemented are spread over multiple lines, there should be exactly 1 interface mentioned per line indented by 1 level.  The closing brace of the class must go on the first line after the body of the class and must be on a line by itself.
  <table>
   <tr>
    <th>Valid: Correct spacing around class keyword.</th>
    <th>Invalid: 2 spaces used around class keyword.</th>
   </tr>
   <tr>
<td>

    abstract class Foo
    {
    }

</td>
<td>

    abstract  class  Foo
    {
    }

</td>
   </tr>
  </table>
## Property Declarations
Property names should not be prefixed with an underscore to indicate visibility.  Visibility should be used to declare properties rather than the var keyword.  Only one property should be declared within a statement.  The static declaration must come after the visibility declaration.
  <table>
   <tr>
    <th>Valid: Correct property naming.</th>
    <th>Invalid: An underscore prefix used to indicate visibility.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        private $bar;
    }

</td>
<td>

    class Foo
    {
        private $_bar;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Visibility of property declared.</th>
    <th>Invalid: Var keyword used to declare property.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        private $bar;
    }

</td>
<td>

    class Foo
    {
        var $bar;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: One property declared per statement.</th>
    <th>Invalid: Multiple properties declared in one statement.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        private $bar;
        private $baz;
    }

</td>
<td>

    class Foo
    {
        private $bar, $baz;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: If declared as static, the static declaration must come after the visibility declaration.</th>
    <th>Invalid: Static declaration before the visibility declaration.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        public static $bar;
        private $baz;
    }

</td>
<td>

    class Foo
    {
        static protected $bar;
    }

</td>
   </tr>
  </table>
## Scope Keyword Spacing
The php keywords static, public, private, and protected should have one space after them.
  <table>
   <tr>
    <th>Valid: A single space following the keywords.</th>
    <th>Invalid: Multiple spaces following the keywords.</th>
   </tr>
   <tr>
<td>

    public static function foo()
    {
    }

</td>
<td>

    public  static  function foo()
    {
    }

</td>
   </tr>
  </table>
## Method Declarations
Method names should not be prefixed with an underscore to indicate visibility.  The static keyword, when present, should come after the visibility declaration, and the final and abstract keywords should come before.
  <table>
   <tr>
    <th>Valid: Correct method naming.</th>
    <th>Invalid: An underscore prefix used to indicate visibility.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        private function bar()
        {
        }
    }

</td>
<td>

    class Foo
    {
        private function _bar()
        {
        }
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Correct ordering of method prefixes.</th>
    <th>Invalid: static keyword used before visibility and final used after.</th>
   </tr>
   <tr>
<td>

    class Foo
    {
        final public static function bar()
        {
        }
    }

</td>
<td>

    class Foo
    {
        static public final function bar()
        {
        }
    }

</td>
   </tr>
  </table>
## Lowercase Function Keywords
The php keywords function, public, private, protected, and static should be lowercase.
  <table>
   <tr>
    <th>Valid: Lowercase function keyword.</th>
    <th>Invalid: Uppercase function keyword.</th>
   </tr>
   <tr>
<td>

    function foo()
    {
        return true;
    }

</td>
<td>

    FUNCTION foo()
    {
        return true;
    }

</td>
   </tr>
  </table>
## Default Values in Function Declarations
Arguments with default values go at the end of the argument list.
  <table>
   <tr>
    <th>Valid: argument with default value at end of declaration</th>
    <th>Invalid: argument with default value at start of declaration</th>
   </tr>
   <tr>
<td>

    function connect($dsn, $persistent = false)
    {
        ...
    }

</td>
<td>

    function connect($persistent = false, $dsn)
    {
        ...
    }

</td>
   </tr>
  </table>
## Function Argument Spacing
Function arguments should have one space after a comma, and single spaces surrounding the equals sign for default values.
  <table>
   <tr>
    <th>Valid: Single spaces after a comma.</th>
    <th>Invalid: No spaces after a comma.</th>
   </tr>
   <tr>
<td>

    function foo($bar, $baz)
    {
    }

</td>
<td>

    function foo($bar,$baz)
    {
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Single spaces around an equals sign in function declaration.</th>
    <th>Invalid: No spaces around an equals sign in function declaration.</th>
   </tr>
   <tr>
<td>

    function foo($bar, $baz = true)
    {
    }

</td>
<td>

    function foo($bar, $baz=true)
    {
    }

</td>
   </tr>
  </table>
## Foreach Loop Declarations
There should be a space between each element of a foreach loop and the as keyword should be lowercase.
  <table>
   <tr>
    <th>Valid: Correct spacing used.</th>
    <th>Invalid: Invalid spacing used.</th>
   </tr>
   <tr>
<td>

    foreach ($foo as $bar => $baz) {
        echo $baz;
    }

</td>
<td>

    foreach ( $foo  as  $bar=>$baz ) {
        echo $baz;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Lowercase as keyword.</th>
    <th>Invalid: Uppercase as keyword.</th>
   </tr>
   <tr>
<td>

    foreach ($foo as $bar => $baz) {
        echo $baz;
    }

</td>
<td>

    foreach ($foo AS $bar => $baz) {
        echo $baz;
    }

</td>
   </tr>
  </table>
## For Loop Declarations
In a for loop declaration, there should be no space inside the brackets and there should be 0 spaces before and 1 space after semicolons.
  <table>
   <tr>
    <th>Valid: Correct spacing used.</th>
    <th>Invalid: Invalid spacing used inside brackets.</th>
   </tr>
   <tr>
<td>

    for ($i = 0; $i < 10; $i++) {
        echo $i;
    }

</td>
<td>

    for ( $i = 0; $i < 10; $i++ ) {
        echo $i;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Correct spacing used.</th>
    <th>Invalid: Invalid spacing used before semicolons.</th>
   </tr>
   <tr>
<td>

    for ($i = 0; $i < 10; $i++) {
        echo $i;
    }

</td>
<td>

    for ($i = 0 ; $i < 10 ; $i++) {
        echo $i;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Correct spacing used.</th>
    <th>Invalid: Invalid spacing used after semicolons.</th>
   </tr>
   <tr>
<td>

    for ($i = 0; $i < 10; $i++) {
        echo $i;
    }

</td>
<td>

    for ($i = 0;$i < 10;$i++) {
        echo $i;
    }

</td>
   </tr>
  </table>
## Lowercase Control Structure Keywords
The php keywords if, else, elseif, foreach, for, do, switch, while, try, and catch should be lowercase.
  <table>
   <tr>
    <th>Valid: Lowercase if keyword.</th>
    <th>Invalid: Uppercase if keyword.</th>
   </tr>
   <tr>
<td>

    if ($foo) {
        $bar = true;
    }

</td>
<td>

    IF ($foo) {
        $bar = true;
    }

</td>
   </tr>
  </table>
## Inline Control Structures
Control Structures should use braces.
  <table>
   <tr>
    <th>Valid: Braces are used around the control structure.</th>
    <th>Invalid: No braces are used for the control structure..</th>
   </tr>
   <tr>
<td>

    if ($test) {
        $var = 1;
    }

</td>
<td>

    if ($test)
        $var = 1;

</td>
   </tr>
  </table>
## Elseif Declarations
PHP's elseif keyword should be used instead of else if.
  <table>
   <tr>
    <th>Valid: Single word elseif keyword used.</th>
    <th>Invalid: Separate else and if keywords used.</th>
   </tr>
   <tr>
<td>

    if ($foo) {
        $var = 1;
    } elseif ($bar) {
        $var = 2;
    }

</td>
<td>

    if ($foo) {
        $var = 1;
    } else if ($bar) {
        $var = 2;
    }

</td>
   </tr>
  </table>
## Switch Declarations
Case statements should be indented 4 spaces from the switch keyword.  It should also be followed by a space.  Colons in switch declarations should not be preceded by whitespace.  Break statements should be indented 4 more spaces from the case statement.  There must be a comment when falling through from one case into the next.
  <table>
   <tr>
    <th>Valid: Case statement indented correctly.</th>
    <th>Invalid: Case statement not indented 4 spaces.</th>
   </tr>
   <tr>
<td>

    switch ($foo) {
        case 'bar':
            break;
    }

</td>
<td>

    switch ($foo) {
    case 'bar':
        break;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Case statement followed by 1 space.</th>
    <th>Invalid: Case statement not followed by 1 space.</th>
   </tr>
   <tr>
<td>

    switch ($foo) {
        case 'bar':
            break;
    }

</td>
<td>

    switch ($foo) {
        case'bar':
            break;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Colons not prefixed by whitespace.</th>
    <th>Invalid: Colons prefixed by whitespace.</th>
   </tr>
   <tr>
<td>

    switch ($foo) {
        case 'bar':
            break;
        default:
            break;
    }

</td>
<td>

    switch ($foo) {
        case 'bar' :
            break;
        default :
            break;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Break statement indented correctly.</th>
    <th>Invalid: Break statement not indented 4 spaces.</th>
   </tr>
   <tr>
<td>

    switch ($foo) {
        case 'bar':
            break;
    }

</td>
<td>

    switch ($foo) {
        case 'bar':
        break;
    }

</td>
   </tr>
  </table>
  <table>
   <tr>
    <th>Valid: Comment marking intentional fall-through.</th>
    <th>Invalid: No comment marking intentional fall-through.</th>
   </tr>
   <tr>
<td>

    switch ($foo) {
        case 'bar':
        // no break
        default:
            break;
    }

</td>
<td>

    switch ($foo) {
        case 'bar':
        default:
            break;
    }

</td>
   </tr>
  </table>
## Cast Whitespace
Casts should not have whitespace inside the parentheses.
  <table>
   <tr>
    <th>Valid: No spaces.</th>
    <th>Invalid: Whitespace used inside parentheses.</th>
   </tr>
   <tr>
<td>

    $foo = (int)'42';

</td>
<td>

    $foo = ( int )'42';

</td>
   </tr>
  </table>
Documentation generated on Sun, 08 Mar 2020 20:15:35 +0100 by [PHP_CodeSniffer 3.5.4](https://github.com/squizlabs/PHP_CodeSniffer)
