<?php
/*
 * Settings
 */
// Sets the names displayed on the multiple choice buttons
$MultiChoiceButtons = array(
    'Cat1', 'Cat2', 'Cat3', 'Cat4', 'Cat6', 'Cat6',
    'Cat7', 'Cat 8', 'Cat9', 'Cat10', 'Cat11', 'Cat 12',
);

// Sets how many items are displayed per row
// use values 1-4;anything bigger causes problems which require css changes
$MCitemsPerRow = 4;

/**
 * Explodes a string and trims all the resultant values.
 * 
 * @param string $delim  The delimiter to explode on.
 * @param string $string The string to explode.
 *
 * @return array The array of trimmed values.
 */
function trimExplode($delim, $string)
{
    $explode = explode($delim, $string);
    foreach ($explode as &$str) {
        $str = trim($str);
    }

    return $explode;
}

/*
 * Set up MC button grid
 */
// shuffle button positions (first time only) and save to session
if (isset($_SESSION['MCbutton']) === false) {
    $mc = $MultiChoiceButtons;// set this above in Settings
    shuffle($mc);// comment out this line to prevent shuffling
    $_SESSION['MCbutton'] = $mc;
} else {
    $mc = $_SESSION['MCbutton'];
}

// load setting for items per row (above in Settings)
$perRow = $perCol = $MCitemsPerRow;

// get cues and answers
$cues = trimExplode('|', $_EXPT->get('cue'));
$answers = trimExplode('|', $_EXPT->get('answer'));

$buttons = array();
$limitPerRow = true;
$horizontal = true;
$share = false;

$settings = trimExplode('|', $_EXPT->get('settings'));
$stimCols = array();
foreach ($_TRIAL['Stimuli'] as $column => $notImportant) {
    $stimCols[strtolower($column)] = $column;
}

foreach ($settings as $setting) {
    $theseAreButtons = false;
    $shuffleThese = false;

    if (Collector\Helpers::removeLabel($setting, 'button') !== false) {
        $theseAreButtons = true;
    } elseif (Collector\Helpers::removeLabel($setting, 'perRow') !== false) {
        $test = Collector\Helpers::removeLabel($setting, 'perRow');
        if (is_numeric($test)) {
            $perRow = (int) $test;
            $limitPerRow = true;
        }
    } elseif (Collector\Helpers::removeLabel($setting, 'perColumn') !== false) {
        $test = Collector\Helpers::removeLabel($setting, 'perColumn');
        if (is_numeric($test)) {
            $perCol = (int) $test;
            $limitPerRow = false;
        }
    } elseif (Collector\Helpers::removeLabel($setting, 'horizontal') !== false) {
        $horizontal = true;
    } elseif (Collector\Helpers::removeLabel($setting, 'vertical') !== false) {
        $horizontal = false;
    } elseif (Collector\Helpers::removeLabel($setting, 'shuffle') !== false) {
        $unlabeled = Collector\Helpers::removeLabel($setting, 'shuffle');
        if (Collector\Helpers::removeLabel($unlabeled, 'button') !== false) {
            $setting = $unlabeled;
            $theseAreButtons = true;
        } else {
            shuffle($buttons);
        }
    } elseif (Collector\Helpers::removeLabel($setting, 'share') !== false) {
        $share = Collector\Helpers::removeLabel($setting, 'share');
    } else {
        $theseAreButtons = true;
    }

    if ($theseAreButtons) {
        $theseButtons = trimExplode(';', Collector\Helpers::removeLabel($setting, 'button'));
        $newButtons = array();
        foreach ($theseButtons as $thisButton) {
            if ($thisButton === '') {
                continue;
            }
            if ($thisButton[0] === '$') {
                $sep = strrpos($thisButton, '[');
                if ($sep === false) {
                    $col = substr($thisButton, 1);
                    $index = $item;
                } else {
                    $col = substr($thisButton, 1, $sep - 1);
                    $index = substr($thisButton, $sep + 1, strrpos($thisButton, ']') - $sep - 1);
                }
                $col = strtolower(trim($col));
                if (isset($stimCols[$col])) {
                    $index = Collector\Helpers::rangeToArray($index);
                    foreach ($index as $i) {
                        $newButtons[] = $_EXPT->stimuli[$i - 2][$stimCols[$col]];
                    }
                } else {
                    $newButtons[] = $thisButton;// so we can see which button is being outputted as $bad button [2o3nri...
                    $trialFail = true;
                    echo '<h3>Buttons incorrectly defined. For dynamic buttons, please use a dollar sign, followed by the column name, followed by a space, followed by a number or range, like <strong>$cue[2::8]</strong></h3>';
                }
            } else {
                $newButtons[] = $thisButton;
            }
        }
        if ($shuffleThese) {
            shuffle($newButtons);
        }
        $buttons = array_merge($buttons, $newButtons);
    }
}
if ($buttons === array()) {
    $buttons = $_SESSION['MCbutton'];
}
$buttons_unique = array_unique($buttons);

if (!isset($_TRIAL['Response']['Buttons'])) {
    if ($share !== false) {
        if (!isset($_SESSION['Share'][$share]['Buttons'])) {
            $_SESSION['Share'][$share]['Buttons'] = $buttons_unique;
        } else {
            $buttons_unique = $_SESSION['Share'][$share]['Buttons'];
        }
    }
    $_TRIAL['Response']['Buttons'] = implode('|', $buttons_unique);
} else {
    $buttons_unique = explode('|', $_TRIAL['Response']['Buttons']);
}

$buttonGrid = array();
$x = 0;
$y = 0;

$count = count($buttons_unique);
if ($limitPerRow and $horizontal) {
    $numCols = min($perRow, $count);
} elseif (!$limitPerRow and !$horizontal) {
    $numRows = min($perCol, $count);
} elseif (!$limitPerRow and $horizontal) {
    $numCols = (int) ceil($count / min($perCol, $count));
} else {                // ($limitPerRow AND !$horizontal)
    $numCols = (int) ceil($count / min($perCol, $count));
    $numRows = (int) ceil($count / $numCols);
    $rem = $count % $numCols;
}

foreach ($buttons_unique as $button) {
    $buttonGrid[$y][$x] = $button;
    if ($horizontal) {
        ++$x;
    } else {
        ++$y;
    }
    if ($horizontal && $x === $numCols) {
        $x = 0;
        ++$y;
    } elseif (!$horizontal && $y === $numRows) {
        $y = 0;
        ++$x;
        --$rem;
        if ($rem === 1) {
            --$perCol;
        }
    }
}

$tdWidth = 78 / count($buttonGrid[0]);
?>

<style type="text/css"> 
  .mcPicTable td {
    width:<?= $tdWidth ?>%;
  }
</style>

<!-- show the image -->
<div class="pic"><?= Collector\Helpers::show($cues[0]) ?></div>

<!-- optional text -->
<div><?= $_EXPT->get('text') ?></div>

<!-- button grid -->
<table class="mcPicTable">
  <?php foreach ($buttonGrid as $row): ?>
  <tr>
    <?php foreach ($row as $field): ?>
    <td><div class="collectorButton TestMC"><?= $field ?></div></td>
    <?php endforeach;?>
  </tr>
  <?php endforeach;?>
</table>

<input class="hidden" name="Response" id="Response" type="text" value="">
<button class="hidden" id="FormSubmitButton">Submit</button>
  
<script>
  // updates the response value when a MC button is pressed
  $(".TestMC").click(function() {
    var clicked = $(this).html();
    var form = $("form");
    
    // record which button was clicked
    $("#Response").prop("value",clicked);
    
    // set RT
    $("#RT").val( COLLECTOR.getRT() );
    form.addClass("submitAfterMinTime");

    // if UserTiming, submit, but only highlight choice otherwise
    if (form.hasClass("UserTiming") && !form.hasClass("WaitingForMinTime")) {
      form.submit();// see common:init "intercept FormSubmitButton"
    } else {
      // set first keypress times
      if (typeof keypress !== 'undefined') {
        $("#RTfirst").val( COLLECTOR.getRT() );
        keypress = true;
      }
      
      // update 'RTlast' time
      $("#RTlast").val( COLLECTOR.getRT() );

      // remove highlighting from all buttons
      $(".TestMC").removeClass("collectorButtonActive");
      
      // add highlighting to clicked button
      $(this).addClass("collectorButtonActive");
    }
  });
</script>
