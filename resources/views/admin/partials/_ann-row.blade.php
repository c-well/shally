      <div class="ann-wrap {{ trim((string) $a->title) === '' ? 'child' : '' }} {{ $a->is_web_only ? 'webonly' : '' }}" data-aid="{{ $a->id }}">
        <div class="item">
          <span class="drag" title="Drag to reorder"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg></span>
          <div class="fields" style="grid-template-columns:1fr 1.6fr">
            <input data-af="title" value="{{ $a->title }}" placeholder="Title (blank = bullets for the section above)">
            <input data-af="detail" value="{{ $a->detail }}" placeholder="Detail">
          </div>
          <div class="ctrls">
            <button class="ic aup" title="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
            <button class="ic adown" title="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
            <button class="ic ann-media-toggle {{ ($a->image_path || $a->video_url) ? 'on' : '' }}" title="Image / video">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </button>
            <button class="ic adel" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>
          </div>
        </div>
        <div class="ann-media">
          <label class="ann-photo">
            <span class="ann-photo-state">{{ $a->image_path ? 'Image attached — tap to replace' : 'Add an image' }}</span>
            <input type="file" class="ann-img" accept="image/*">
          </label>
          @if ($a->image_path)<img class="ann-thumb" src="/{{ $a->image_path }}" alt="">@endif
          <button type="button" class="ann-img-remove" data-url="{{ route('bulletins.announcements.image.remove', [$bulletin, $a]) }}" style="{{ $a->image_path ? '' : 'display:none' }}">Remove image</button>
          <label class="ann-vid-lbl">Video link <input data-af="video_url" value="{{ $a->video_url }}" placeholder="YouTube or Vimeo URL"></label>
        </div>
      </div>
