    <div id="playPopup">
      <div id="videoPlayer">
        <br />
        <a href="http://www.macromedia.com/go/getflashplayer" class="highlight" target="_blank">
          Get the Flash Player (version 10 or higher)
        </a>
        to see this content.
      </div>
      <table id="clipInfo" cellspacing="0" border="0">
        <tr>
          <th>Code:</th>
          <td id="clipCode"></td>
          <th>Title:</th>
          <td id="clipTitle"></td>
        </tr>
        <tr>
          <th>Description:</th>
          <td id="clipDescription"></td>
          <th>Keywords:</th>
          <td id="clipKeywords"></td>
        </tr>
      </table>
      
      <?if(!($is_admin||$is_editor)){?>
      <div id="moreInfo">
        <img src="/data/img/info.gif" alt="" width="30" height="30" align="absmiddle" />
        <span>More Info</span>
      </div>

      <div id="popupButtons">
        <form method="post" action="<?=$lang?>/cart/add" target="cart">
          <input type="hidden" name="type" value="2" />
          <input type="hidden" name="id" id="cartClipId" />
          <button class="button" type="submit">Add to cart</button>
        </form>
        <form method="post" action="<?=$lang?>/bin/add" target="cart">
          <input type="hidden" name="type" value="2" />
          <input type="hidden"  name="id" id="binClipId" />
          <button class="button" type="submit">Add to clipbin</button>
        </form>
      </div>
      <?}?>
    </div>

    <?if(!($is_admin||$is_editor)){?>
    <div id="additionalInfo">
      <table cellspacing="0" border="0">
        <tr>
          <th>Resolution:</th>
          <td id="clipResolution"></td>
        </tr>
        <tr>
          <th>Clip length:</th>
          <td id="clipLength"></td>
        </tr>
        <tr>
          <th>Format:</th>
          <td id="clipFormat"></td>
        </tr>
      </table>

      <p>Other clips in this set (<span id="otherClipsCount"></span>)</p>
      <img src="/data/img/scroll-left.gif" alt="Prev" id="scrollLeft" />
      <div id="otherClips">
        <div id="clipsFeed">
        </div>
      </div>
      <img src="/data/img/scroll-right.gif" alt="Prev" id="scrollRight" />
    </div>
    <?}?>