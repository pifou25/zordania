<pre><?php
class animal {
	var $color;
	var $race;
	var $genre;
	function __construct(string $color, string $race, string $genre){
		$this->color = $color;
		$this->race = $race;
		$this->genre = $genre;
	}
}

$ricard = new animal('jaune', '25cl', 'liquide');
$rhum = new animal('ambre', '4cl', 'liquide');

$a = ['too' => 'premier element', 'ricard' => $ricard];

$b = $a;

$a['rhum'] = $rhum;
$a['too'] = 'copie du premier element';
$rhum->genre = 'alcool';

echo "b = \n";
var_dump($b);

echo "a = \n";
var_dump($a);


?>
</pre>