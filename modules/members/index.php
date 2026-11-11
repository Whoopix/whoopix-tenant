<?php
	if(!is_array($USER_LOGIN)) {
		include "login.php";
	} else {
		$TEMP_PAGE_TITLE   = "פרופיל";
		$TEMP_INPAGE_TITLE = "פרופיל";

		$error    = true;   // becomes false only on a successful save
		$err      = "";     // message text shown above the form
		$msg_type = "error"; // error | success

		if (isset($_POST["sub"]) && $_POST["sub"] === "SUBTRUE") {

			$error = false;

			$Pname     = db_escape(htmlspecialchars((string)($_POST["name"]     ?? '')));
			$Pemail    = db_escape(htmlspecialchars((string)($_POST["email"]    ?? '')));
			$Pusername = db_escape(htmlspecialchars((string)($_POST["username"] ?? '')));
			$Pphone    = db_escape((string)($_POST["phone"] ?? ''));
			$Pfname    = db_escape(htmlspecialchars((string)($_POST["fname"] ?? ''))); // "אודות" / bio
			$Psex      = (int)($_POST["sex"] ?? 0);
			$Pcell     = db_escape((string)($_POST["cell"] ?? ''));               // אזור / עיר
			$Pnewsletter = intval($_POST["newsletter"] ?? 0);

			// ── current row (for existing avatar + gender fallback) ──────────────
			$for_pic_array = __fetch_row(
				"SELECT * FROM `mod_members` WHERE `id` = :id",
				['id' => (int)$USER_LOGIN["id"]]
			);

			$pic_url = $for_pic_array['pic'] ?? '';

			// ── avatar upload (עדכן תמונה) ──────────────────────────────────────
			if (isset($_FILES['pic']) && !empty($_FILES['pic']['name']) && (int)($_FILES['pic']['error'] ?? 4) === UPLOAD_ERR_OK) {

				$file_name = (string) $_FILES['pic']['name'];
				$file_size = (int) $_FILES['pic']['size'];
				$file_tmp  = (string) $_FILES['pic']['tmp_name'];
				$file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
				$allowed   = array("jpeg", "jpg", "png");

				if (!in_array($file_ext, $allowed, true)) {
					$error = true;
					$err   = "סיומת תמונה לא חוקית - ניתן להעלות רק קבצים בפורמט PNG או JPG";
				} elseif ($file_size > 2097152) {
					$error = true;
					$err   = "גודל הקובץ המירבי הוא 2MB";
				} else {
					$upload_dir = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . '/files/user_pic';
					if (!is_dir($upload_dir)) {
						@mkdir($upload_dir, 0755, true);
					}
					$new_name = md5(uniqid("", true) . time()) . '.' . $file_ext;
					if (@move_uploaded_file($file_tmp, $upload_dir . '/' . $new_name)) {
						$pic_url = $new_name;
					} else {
						$error = true;
						$err   = "שמירת התמונה נכשלה, נסה שוב";
					}
				}
			} elseif (empty($pic_url)) {
				// no avatar yet → pick a random gendered placeholder (mirrors register.php)
				$dir   = ($Psex === 1) ? 'files/random_user/male' : 'files/random_user/female';
				$files = @glob($dir . '/*.*');
				if (!empty($files)) {
					$pic_url = $files[array_rand($files)];
				}
			}

			// ── validation ──────────────────────────────────────────────────────
			$check_e = db_query(
				"SELECT id FROM `mod_members` WHERE `email` = :e",
				['e' => (string)($_POST["email"] ?? '')]
			);

			if (!$error) {
				if (trim($Pname) == "") {
					$error = true;
					$err   = "אנא מלא שם";
				} elseif (trim($Pemail) != "" && !preg_match("/^([a-zA-Z0-9]+)([a-zA-Z0-9\._\-]+)*@([a-zA-Z0-9])+([a-zA-Z0-9\-][a-zA-Z0-9]+)*(\.[a-zA-Z0-9]{2,3}){1,2}$/", $Pemail)) {
					$error = true;
					$err   = "כתובת דוא\"ל לא חוקית";
				} elseif (trim($Pemail) != "" && db_num_rows($check_e) > 0 && $Pemail != $USER_LOGIN["email"]) {
					$error = true;
					$err   = "כתובת דוא\"ל קיימת במערכת";
				} elseif (trim((string)($_POST["pass"] ?? "")) != "" && $_POST["pass"] != ($_POST["pass2"] ?? "")) {
					$error = true;
					$err   = "סיסמאות חדשות לא תואמות";
				} elseif (trim((string)($_POST["pass"] ?? "")) != "" && strlen(trim((string)$_POST["pass"])) < 6) {
					$error = true;
					$err   = "הסיסמה החדשה חייבת להכיל לפחות 6 תווים";
				}
			}

			// ── persist ──────────────────────────────────────────────────────────
			if (!$error) {
				$msg_type = "success";
				$err      = "הפרטים עודכנו בהצלחה";

				$has_pass = trim((string)($_POST["pass"] ?? "")) != "";
				$Ppass    = $has_pass ? md5((string)$_POST["pass"]) : "";

				$sql = "UPDATE `mod_members` SET "
					. (trim($Pemail) != "" ? "`email`='$Pemail'," : "")
					. ($has_pass ? "`pass`='$Ppass'," : "")
					. "`name`='$Pname',`fname`='$Pfname',`sex`='$Psex',`pic`='".db_escape($pic_url)."',"
					. "`phone`='$Pphone',`cell`='$Pcell',`username`='$Pusername',`newsletter`='$Pnewsletter' "
					. "WHERE `id`=" . (int)$USER_LOGIN["id"];

				db_query($sql) or die(db_error());

				// keep the session/cookie auth token in sync after a password change
				if ($has_pass) {
					if (isset($_SESSION["user"]) && $_SESSION["user"] != null) {
						$_SESSION["user"] = intval($USER_LOGIN["id"]) . "_" . $Ppass;
					}
					if (isset($_COOKIE["USER"])) {
						setcookie("USER[pass]", $Ppass, time() + 60 * 60 * 24 * 31 * 12);
					}
				}

				// refresh the in-memory user so the form re-renders updated values
				$USER_LOGIN = loggedIn();
			}
		}

	function PAGE_TEXT() {
		global $error, $err, $msg_type, $USER_LOGIN;

		$USER_LOGIN = loggedIn();

		$Vname     = strip($USER_LOGIN["name"]     ?? '');
		$Vphone    = strip($USER_LOGIN["phone"]    ?? '');
		$Vemail    = strip($USER_LOGIN["email"]    ?? '');
		$Vfname    = strip($USER_LOGIN["fname"]    ?? '');
		$Vusername = strip($USER_LOGIN["username"] ?? '');
		$Vsex      = strip($USER_LOGIN["sex"]      ?? '');
		$Vcell     = strip($USER_LOGIN["cell"]     ?? '');

		// resolve avatar (matches the upload + display convention)
		$__user_pic = 'resources/design/images/coment-avatar-3.jpg';
		$__pic = (string)($USER_LOGIN['pic'] ?? '');
		if ($__pic !== '') {
			if (file_exists(rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . '/files/user_pic/' . $__pic)) {
				$__user_pic = '/files/user_pic/' . $__pic;
			} elseif (file_exists(rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . '/' . $__pic)) {
				// stored as a relative path (random placeholder)
				$__user_pic = '/' . ltrim($__pic, '/');
			}
		}
		?>

  <div class="member-dashboard profile-settings">
        <div class="dashboard-layout">

          <?php $ACCOUNT_NAV_ACTIVE = 'profile'; include __DIR__ . "/inc/account_sidebar.php"; ?>

          <div class="dashboard-main">

            <?php if (!empty($err)) { ?>
              <div id="error123" style="font-weight:bold;width:100%;text-align:center;padding:14px;margin-bottom:18px;border-radius:14px;<?= $msg_type === 'success' ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;' ?>">
                <?= $err ?>
              </div>
            <?php } ?>

            <form class="settings-form" action="" method="post" enctype="multipart/form-data">

            <div class="settings-header">
              <div class="avatar-upload">
                <img id="avatarPreview" src="<?php echo $__user_pic; ?>" alt="תמונת משתמש" />
                <label class="upload-label">
                  <input type="file" name="pic" accept=".jpg,.jpeg,.png" onchange="bpAvatarPreview(this)" />
                  <i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i>
                  עדכן תמונה
                </label>
              </div>
              <div class="settings-header-content">
                <span>פרופיל משתמש</span>
                <h2>ניהול חשבון משתמש</h2>
                <p>
                  עדכן את פרטי המשתמש שלך כדי לשמור על פרופיל מעודכן במערכת ולאפשר ניהול נוח של החשבון והעסקים שלך.
                </p>
                <div class="settings-header-meta">
                  <span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> משתמש פעיל במערכת</span>
                  <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ישראל</span>
                  <span><i class="fa-solid fa-clock" aria-hidden="true"></i> עודכן לאחרונה</span>
                </div>
              </div>
            </div>

              <div class="alert alert-info" style="margin-bottom:20px;border-radius:14px;">
                <i class="fa-solid fa-circle-info"></i>
                פרופיל זה מיועד לעריכת פרטי המשתמש בלבד. ניהול העסקים מתבצע בנפרד דרך תפריט "ניהול עסקים".
              </div>

              <input type="hidden" name="sub" value="SUBTRUE">
              <input type="hidden" name="newsletter" value="0">

              <div class="settings-grid">

                <div>
                  <label class="form-label">שם מלא</label>
                  <input type="text" class="form-control" name="name" value="<?=$Vname?>" />
                </div>

                <div>
                  <label class="form-label">שם משתמש</label>
                  <input type="text" class="form-control" name="username" value="<?=$Vusername?>" />
                </div>

                <div>
                  <label class="form-label">אימייל</label>
                  <input type="email" class="form-control" name="email" value="<?=$Vemail?>" />
                </div>

                <div>
                  <label class="form-label">טלפון</label>
                  <input type="text" class="form-control" name="phone" value="<?=$Vphone?>" />
                </div>

                <div>
                  <label class="form-label">אזור / עיר</label>
                  <input type="text" class="form-control" name="cell" value="<?=$Vcell?>" />
                </div>

                <div>
                  <label class="form-label">מין</label>
                  <select class="form-select" name="sex">
                    <option value="1" <?php if($Vsex == 1){ echo 'selected'; } ?>>זכר</option>
                    <option value="2" <?php if($Vsex == 2){ echo 'selected'; } ?>>נקבה</option>
                  </select>
                </div>

                <div class="grid-span-2">
                  <label class="form-label">אודות</label>
                  <textarea class="form-control" name="fname" rows="5"><?=$Vfname?></textarea>
                </div>

                <div>
                  <label class="form-label">סיסמה חדשה</label>
                  <input type="password" class="form-control" name="pass" autocomplete="new-password" />
                </div>

                <div>
                  <label class="form-label">אימות סיסמה</label>
                  <input type="password" class="form-control" name="pass2" autocomplete="new-password" />
                </div>

              </div>

              <div class="form-actions">
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href=',members';">
                  ביטול
                </button>
                <button type="submit" class="btn btn-primary">
                  שמירת שינויים
                </button>
              </div>
            </form>
          </div>
        </div>
  </div>

    <script>
    function bpAvatarPreview(input){
      if(input.files && input.files[0]){
        var reader = new FileReader();
        reader.onload = function(e){
          var img = document.getElementById('avatarPreview');
          if(img){ img.src = e.target.result; }
        };
        reader.readAsDataURL(input.files[0]);
      }
    }
    </script>

<?php
	}
		$TEMP_TEMPLATE = 5;
	}
?>
