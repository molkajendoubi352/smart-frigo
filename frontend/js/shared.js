const BACKEND = "../../backend";

async function api(file, action, data = {}) {
  const user = getUser();
  let userId = 0;
  if (user) {
    userId = user.id;
  }

  const payload = { action: action, user_id: userId };
  for (const key in data) {
    payload[key] = data[key];
  }

  const response = await fetch(BACKEND + "/" + file, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  const json = await response.json();
  return json;
}

function getUser() {
  const u = sessionStorage.getItem("sf_user");
  if (!u) {
    return null;
  }
  return JSON.parse(u);
}

function setUser(user) {
  if (user) {
    sessionStorage.setItem("sf_user", JSON.stringify(user));
  } else {
    sessionStorage.removeItem("sf_user");
  }
}

function requireAuth() {
  if (!getUser()) {
    window.location.href = "login.html";
  }
}

function expiryStatus(dateStr) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const exp = new Date(dateStr);
  const diff = exp - today;
  const days = Math.ceil(diff / 86400000);

  if (days < 0) {
    return { cls: "expired", label: "Expired " + Math.abs(days) + "d ago" };
  }
  if (days <= 3) {
    return { cls: "soon", label: "Expires in " + days + "d" };
  }
  return { cls: "ok", label: exp.toLocaleDateString() };
}

function showToast(msg, type = "info") {
  const box = document.getElementById("toast-container");
  if (!box) {
    return;
  }
  const t = document.createElement("div");
  t.className = "toast " + type;
  t.textContent = msg;
  box.appendChild(t);
  setTimeout(function () {
    t.remove();
  }, 3000);
}

function openModal(id) {
  document.getElementById(id).classList.add("show");
}
function closeModal(id) {
  document.getElementById(id).classList.remove("show");
}

function renderSidebar(active, alertCount = 0) {
  const user = getUser() || { name: "User", initials: "US" };
  let badge = "";
  if (alertCount) {
    badge = '<span class="nav-badge">' + alertCount + '</span>';
  }

  const links = [
    { page: "dashboard", href: "dashboard.html", icon: "🏠", label: "Home" },
    { page: "fridges", href: "fridges.html", icon: "🧊", label: "My Fridges" },
    { page: "products", href: "products.html", icon: "🥦", label: "Products" },
    { page: "history", href: "history.html", icon: "📈", label: "History" },
    { page: "alerts", href: "alerts.html", icon: "🔔", label: "Alerts " + badge }
  ];

  let navHTML = "";
  for (let i = 0; i < links.length; i++) {
    const link = links[i];
    const activeClass = active === link.page ? " active" : "";
    navHTML += '<a class="nav-item' + activeClass + '" href="' + link.href + '">';
    navHTML += '<span class="nav-icon">' + link.icon + '</span> ' + link.label;
    navHTML += '</a>';
  }

  return "\n  <aside class=\"sidebar\">\n    <div class=\"sidebar-logo\">\n      <div class=\"icon\">❄️</div>\n      <h2>Smart<span>Frigo</span></h2>\n    </div>\n    <nav>\n      <div class=\"nav-section\">Main</div>\n      " + navHTML + "\n    </nav>\n    <div class=\"sidebar-user\">\n      <div class=\"user-avatar\">" + user.initials + "</div>\n      <div class=\"user-info\">\n        <div class=\"user-name\">" + user.name + "</div>\n        <div class=\"user-role\">" + (user.email || "") + "</div>\n      </div>\n      <a class=\"logout-btn\" href=\"login.html\" onclick=\"setUser(null)\" title=\"Logout\">⏻</a>\n    </div>\n  </aside>";
}
