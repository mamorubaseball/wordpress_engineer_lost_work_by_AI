/**
 * Codex Invite Page Scripts
 * Phase 2: Form submission via Ajax, validation, feedback display, smooth scroll
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    // 1. Smooth scroll from hero CTA to form
    const ctaButton = document.querySelector('.codex-invite-btn-primary[href^="#"]');
    if (ctaButton) {
      ctaButton.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId && targetId !== '#') {
          const targetEl = document.querySelector(targetId);
          if (targetEl) {
            e.preventDefault();
            targetEl.scrollIntoView({
              behavior: 'smooth',
              block: 'start',
            });
            const emailInput = targetEl.querySelector('#codex-invite-email');
            if (emailInput && !emailInput.disabled) {
              setTimeout(function () {
                emailInput.focus();
              }, 400);
            }
          }
        }
      });
    }

    // 2. Ajax Form Submission
    const form = document.getElementById('codex-invite-form');
    if (!form) {
      return;
    }

    const emailInput = document.getElementById('codex-invite-email');
    const submitBtn = document.getElementById('codex-invite-submit');
    const msgBox = document.getElementById('codex-invite-message');
    const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
    const btnLoader = submitBtn ? submitBtn.querySelector('.btn-loader') : null;

    // Helper: Display Feedback Message
    function showMessage(text, isSuccess) {
      if (!msgBox) return;
      msgBox.textContent = text;
      msgBox.className = 'codex-invite-message ' + (isSuccess ? 'is-success' : 'is-error');
      msgBox.style.display = 'block';
      msgBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Helper: Clear Feedback Message
    function clearMessage() {
      if (!msgBox) return;
      msgBox.textContent = '';
      msgBox.style.display = 'none';
      msgBox.className = 'codex-invite-message';
    }

    // Helper: Toggle Loading State
    function setLoading(isLoading) {
      if (!submitBtn || !emailInput) return;
      if (isLoading) {
        submitBtn.disabled = true;
        emailInput.disabled = true;
        if (btnText) btnText.style.display = 'none';
        if (btnLoader) btnLoader.style.display = 'inline';
      } else {
        submitBtn.disabled = false;
        emailInput.disabled = false;
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
      }
    }

    // Robust Email format validator (RFC 5322 compatible regex)
    function isValidEmail(email) {
      const re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
      return re.test(String(email).trim().toLowerCase());
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearMessage();

      const email = emailInput ? emailInput.value.trim() : '';

      // Client-side validation
      if (!email || !isValidEmail(email)) {
        console.warn('Codex Invite: Invalid email input:', email);
        showMessage('正しいメールアドレスを入力してください。（例: example@gmail.com）', false);
        if (emailInput) emailInput.focus();
        return;
      }

      // Check if localized data exists
      if (typeof ajeCodexData === 'undefined') {
        showMessage('設定の読み込みに失敗しました。ページを再読み込みしてください。', false);
        return;
      }

      // Offline preview simulation
      if (!ajeCodexData.ajaxUrl) {
        setLoading(true);
        setTimeout(function () {
          setLoading(false);
          showMessage('【テスト動作確認】\n招待リクエストを受け付けました。\n確認後、24時間以内に招待をお送りします。\n（入力メール: ' + email + '）', true);
          form.reset();
          if (submitBtn) {
            submitBtn.disabled = true;
            if (btnText) btnText.textContent = 'リクエスト送信完了';
          }
        }, 600);
        return;
      }

      // Quota check
      if (ajeCodexData.isFull) {
        showMessage('現在、招待枠はすべて埋まっています。', false);
        return;
      }

      // Prepare form data before disabling inputs
      const params = new URLSearchParams();
      params.append('action', 'aje_codex_invite');
      params.append('nonce', ajeCodexData.nonce);
      params.append('email', email);

      const websiteUrlInput = form.querySelector('input[name="website_url"]');
      if (websiteUrlInput && websiteUrlInput.value) {
        params.append('website_url', websiteUrlInput.value);
      }

      setLoading(true);

      fetch(ajeCodexData.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: params.toString(),
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          setLoading(false);
          if (data.success) {
            showMessage(data.data.message || '招待リクエストを受け付けました。', true);
            form.reset();
            // Lock form after successful submission
            if (submitBtn) {
              submitBtn.disabled = true;
              if (btnText) btnText.textContent = 'リクエスト送信完了';
            }
            if (emailInput) {
              emailInput.disabled = true;
            }
          } else {
            const errorMsg = data.data && data.data.message
              ? data.data.message
              : '送信に失敗しました。もう一度お試しください。';
            showMessage(errorMsg, false);
          }
        })
        .catch(function () {
          setLoading(false);
          showMessage('通信エラーが発生しました。ネットワーク環境をご確認の上、再度お試しください。', false);
        });
    });
  });
})();
