<h1>CyclonePHP Installer</h1>

<div id="tests">
<? foreach ($tests as $test_name => $test_result) : ?>
	<div class="test">
		<span class="test-name"><?= $test_name ?></span>
		<? if ($test_result) : ?>
		<span class="test-result test-result-success">PASSED</span>
		<? else : ?>
		<span class="test-result test-result-failure">FAILED</span>
		<? endif; ?>
	</div>
<? endforeach; ?>
</div>
