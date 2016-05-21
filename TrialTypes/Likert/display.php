<?php
// Likert Settings
//
// Creates a row of radio buttons for the user to select. Only 1 button
//     may be submitted as the response. Additional settings can be added
//     using a "Settings" column, to control the values available in the
//     scale.
//
//
//
// "Text" column
//
//     In this column, you can multiple entries separated by the pipe ( | )
//         character. The first entry contains the question, which appears
//         at the top of the trial.
//
//     Additional entries may be added to put descriptions over the
//         likert scale. If you have 2 more entries (e.g., 
//         "question | Not at all | Very much"), then these will appear
//         over the left and right sides of the scale. If you have 3
//         additional entries, these will appear over the left, center,
//         and right sides of the scale.
//
//     All entries may reference the $cue and $answer for that trial, by
//         simply placing those keywords in the text. For example, a trial
//         with "apple" as the cue could have "How much do you like: $cue"
//         as the question, and this would show up as
//         "How much do you like: apple". These can be used in the scale
//         descriptors as well.
//
//
//
// "Settings" column (optional)
//
//     If you simply put an entry, such as '7' or 'f', the scale will 
//         be from 1 to 7 (or "a" to "f").
//
//     If you put a range (2 entries separated by two colons '::'), the
//         scale will start from the first entry, and end at the second.
//         Both number ranges (-3::3) and alphabetical ranges (a::f)
//         are fine. You can also define the step size using a # sign 
//         after the range (e.g., 0::1#.5).
//
//     You can also combine ranges using commas, such as "1::3,a::c".
//         Using these, its possible to have duplicate entries, so
//         something like "1,1,1,1::4" is possible. If you want to set
//         the step size for multiple ranges, please enter the step size
//         after each range, like "1::3#.1, a::g # 2, 7::9 #.5"

if ($_trialSettings->likert) {
    $likert = $_trialSettings->likert;
} else {
    $likert = '1::7';
}

$likertOptions = Collector\Helpers::rangeToArray($likert);

$texts = explode('|', $_EXPT->get('text'));
$question = array_shift($texts);
$labelWidth = floor(1000 / max(1, count($texts))) / 10;
$optionWidth = floor(1000 / count($likertOptions)) / 10;

/*
 * Determines which text-alignment class to use during the display of the Likert
 * labels.
 * 
 * @param array $texts
 * @param int   $i     Current iteration in the foreach loop.
 * 
 * @return string The class name to use.
 */
$determineClass = function ($texts, $i) {
    if (count($texts) === 2) {
        if ($i === 0) {
            $class = 'textleft';
        } else {
            $class = 'textright';
        }
    } elseif (count($texts) === 3) {
        if ($i === 0) {
            $class = 'textleft';
        } elseif ($i === 1) {
            $class = 'textcenter';
        } else {
            $class = 'textright';
        }
    } else {
        $class = 'textcenter';
    }

    return $class;
}
?>

<style>
  .likertArea {
      text-align:center;
  }
    
  .likertQuestion {
    margin:0 0 30px 0;
    font-size:1.3em;
  }
    
  .likertLabels {
    display:table;
    margin:10px 0;
    color:#666;
    width:100%;
  }
  .likertLabel {
    display:table-cell;
    padding:8px;
    vertical-align:bottom;
    width:<?= $labelWidth ?>%;
  }
    
  .likertOptions {
    display:table;
    margin:10px 0;
    width:100%;
  }
  .likertOption {
    display:table-cell;
    text-align:center;
    padding:14px 8px 28px 8px;
    vertical-align:bottom;
    width:<?= $optionWidth ?>%;
  }
  .likertOption input {
    margin: 3px;
    padding: 2px;
  }
</style>

<div class="likertArea">
  <div class="likertQuestion">
    <?= $question ?>
  </div>

  <div class="likertLabels">
    <?php foreach ($texts as $i => $label): ?>
    <div class='likertLabel <?= $determineClass($texts, $i) ?>'>
      <?= $label ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="likertOptions">
    <?php foreach ($likertOptions as $option): ?>
    <label class="likertOption"><?= $option ?><br><input type="radio" name="Response" value="<?= $option ?>"></label>
    <?php endforeach ?>
  </div>
</div>

<div class="collector-form-element textcenter">
  <button type="submit" class="collectorButton collectorAdvance">Submit</button>
</div>
