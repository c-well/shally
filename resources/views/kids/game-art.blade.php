@if($kind==='memory_match')
<div class="mini-card back-card"></div><div class="mini-card face-card"><svg viewBox="0 0 64 64"><path d="M12 20C12 7 31 5 32 20C34 5 52 7 52 20C52 34 32 48 32 48S12 34 12 20Z" fill="currentColor"/></svg></div><span class="art-dot dot-one"></span>
@elseif($kind==='word_search')
<div class="mini-search"><span>J</span><span>O</span><span>Y</span><span>A</span><span>B</span><span>C</span><span>D</span><span>E</span><span>F</span></div><span class="art-dot dot-two"></span>
@elseif($kind==='hidden_words')
<div class="mini-trail"><i></i><i></i><i></i><b>★</b></div><span class="art-dot dot-one"></span>
@else
<div class="mini-blocks"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div><span class="art-dot dot-two"></span>
@endif
