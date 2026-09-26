// src/shims/react.ts
var g = globalThis;
var R = g.__SPINE__?.react;
var useState = R.useState;
var useEffect = R.useEffect;
var useCallback = R.useCallback;
var useMemo = R.useMemo;
var useRef = R.useRef;
var createElement = R.createElement;
var createContext = R.createContext;
var useContext = R.useContext;
var Fragment = R.Fragment;
var lazy = R.lazy;
var Suspense = R.Suspense;
var Children = R.Children;
var isValidElement = R.isValidElement;
var memo = R.memo;
var forwardRef = R.forwardRef;

// src/api.ts
function baseUrl() {
  const host = globalThis.__SPINE__;
  return host?.apiBase ?? "http://localhost:8000";
}
function getToken() {
  if (typeof window === "undefined") return null;
  return window.localStorage.getItem("spine_token");
}
async function api(path, options = {}) {
  const headers = {
    Accept: "application/json",
    ...options.body ? { "Content-Type": "application/json" } : {}
  };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;
  const res = await fetch(`${baseUrl()}${path}`, { ...options, headers });
  let data = null;
  try {
    data = await res.json();
  } catch {
  }
  if (!res.ok) {
    const err = data?.message ?? `HTTP ${res.status}`;
    return { ok: false, status: res.status, data, error: err };
  }
  return { ok: true, status: res.status, data };
}

// src/styles.ts
var CSS = `
.spine-sts-card {
  border: 1px solid var(--line-soft);
  border-radius: 12px;
  background: var(--surface-raised);
  padding: 12px 14px;
  color: var(--ink);
}
.spine-sts-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--ink);
  margin: 0 0 8px;
}
.spine-sts-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.spine-sts-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  font-size: 13px;
}
.spine-sts-muted {
  color: var(--ink-muted);
}
.spine-sts-badge {
  border-radius: 999px;
  padding: 1px 8px;
  font-size: 11px;
  font-weight: 500;
  background: var(--accent-soft);
  color: var(--accent-strong);
}
.spine-sts-empty {
  font-size: 13px;
  color: var(--ink-faint);
}
.spine-sts-link {
  color: var(--accent-strong);
  font-size: 12px;
  text-decoration: none;
}
.spine-sts-grid {
  display: flex;
  gap: 8px;
}
.spine-sts-pill {
  flex: 1;
  text-align: center;
  border-radius: 10px;
  padding: 6px 4px;
  background: var(--surface-overlay);
}
.spine-sts-pill b {
  display: block;
  font-size: 16px;
  color: var(--ink);
}
.spine-sts-pill span {
  font-size: 11px;
  color: var(--ink-muted);
}
`;
var injected = false;
function ensureModuleStyles() {
  if (injected || typeof document === "undefined") return;
  injected = true;
  const el = document.createElement("style");
  el.textContent = CSS;
  document.head.appendChild(el);
}

// src/shims/jsx-runtime.ts
var g2 = globalThis;
var R2 = g2.__SPINE__?.react;
var Fragment2 = R2.Fragment;
function jsx(type, props, key) {
  return R2.createElement(type, { ...props, key });
}
var jsxs = jsx;

// src/components.tsx
function useSampleItems() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const load = useCallback(() => {
    api("/api/v1/sample").then((res) => {
      if (res.ok) setItems(res.data?.data ?? []);
      else setError(res.error ?? "Gagal memuat");
    }).finally(() => setLoading(false));
  }, []);
  useEffect(() => {
    load();
  }, [load]);
  return { items, loading, error, reload: load };
}
function CountPills({ items }) {
  const count = items.length;
  return /* @__PURE__ */ jsx(
    "div",
    {
      className: "spine-sts-grid",
      style: { marginBottom: 10 },
      children: /* @__PURE__ */ jsxs("div", { className: "spine-sts-pill", children: [
        /* @__PURE__ */ jsx("b", { children: count }),
        /* @__PURE__ */ jsx("span", { children: "Total item" })
      ] })
    }
  );
}
function SampleWidget() {
  ensureModuleStyles();
  const { items, loading, error } = useSampleItems();
  return /* @__PURE__ */ jsxs("div", { className: "spine-sts-card", children: [
    /* @__PURE__ */ jsx("h3", { className: "spine-sts-title", children: "Sample \u2014 ringkasan" }),
    error && /* @__PURE__ */ jsx("div", { className: "spine-sts-muted", children: error }),
    loading ? /* @__PURE__ */ jsx("div", { className: "spine-sts-empty", children: "Memuat..." }) : /* @__PURE__ */ jsxs(Fragment, { children: [
      /* @__PURE__ */ jsx(CountPills, { items }),
      /* @__PURE__ */ jsx("ul", { className: "spine-sts-list", children: items.slice(0, 3).map((it) => /* @__PURE__ */ jsx("li", { className: "spine-sts-item", children: /* @__PURE__ */ jsxs("span", { className: "spine-sts-muted", children: [
        "#",
        it.id,
        " \u2014 ",
        it.name
      ] }) }, it.id)) })
    ] })
  ] });
}
function SampleTab() {
  ensureModuleStyles();
  const { items, loading, error } = useSampleItems();
  return /* @__PURE__ */ jsxs("div", { className: "spine-sts-card", children: [
    /* @__PURE__ */ jsx("h3", { className: "spine-sts-title", children: "Sample" }),
    error && /* @__PURE__ */ jsx("div", { className: "spine-sts-muted", children: error }),
    loading ? /* @__PURE__ */ jsx("div", { className: "spine-sts-empty", children: "Memuat..." }) : items.length === 0 ? /* @__PURE__ */ jsx("div", { className: "spine-sts-empty", children: "Belum ada sample item." }) : /* @__PURE__ */ jsx("ul", { className: "spine-sts-list", children: items.map((it) => /* @__PURE__ */ jsxs("li", { className: "spine-sts-item", children: [
      /* @__PURE__ */ jsxs("span", { children: [
        /* @__PURE__ */ jsxs("span", { className: "spine-sts-muted", children: [
          "#",
          it.id
        ] }),
        " ",
        /* @__PURE__ */ jsx("span", { children: it.name })
      ] }),
      /* @__PURE__ */ jsxs("span", { className: "spine-sts-muted", children: [
        it.quantity ?? 0,
        " x ",
        it.price ?? 0
      ] })
    ] }, it.id)) })
  ] });
}
function SampleSummary() {
  ensureModuleStyles();
  const { items, loading } = useSampleItems();
  if (loading) return null;
  return /* @__PURE__ */ jsxs("div", { className: "spine-sts-card", children: [
    /* @__PURE__ */ jsx("h3", { className: "spine-sts-title", children: "Sample" }),
    /* @__PURE__ */ jsxs("p", { className: "spine-sts-muted", style: { margin: 0 }, children: [
      "Total ",
      /* @__PURE__ */ jsx("b", { children: items.length }),
      " sample item."
    ] }),
    /* @__PURE__ */ jsx("a", { className: "spine-sts-link", href: "/sample", children: "Buka Sample \u2192" })
  ] });
}

// src/module.ts
var module = {
  id: "sample",
  name: "Sample UI",
  version: "0.1.0",
  translations: {
    en: {
      "tabs.sample": "Sample",
      "sections.summary": "Sample Summary",
      "widgets.quickStats": "Sample \u2014 quick stats"
    },
    id: {
      "tabs.sample": "Contoh",
      "sections.summary": "Ringkasan Contoh",
      "widgets.quickStats": "Contoh \u2014 statistik cepat"
    },
    ko: {
      "tabs.sample": "\uC0D8\uD50C",
      "sections.summary": "\uC0D8\uD50C \uC694\uC57D",
      "widgets.quickStats": "\uC0D8\uD50C \u2014 \uBE60\uB978 \uD1B5\uACC4"
    },
    zh: {
      "tabs.sample": "\u793A\u4F8B",
      "sections.summary": "\u793A\u4F8B\u6458\u8981",
      "widgets.quickStats": "\u793A\u4F8B \u2014 \u5FEB\u901F\u7EDF\u8BA1"
    },
    ja: {
      "tabs.sample": "\u30B5\u30F3\u30D7\u30EB",
      "sections.summary": "\u30B5\u30F3\u30D7\u30EB\u6982\u8981",
      "widgets.quickStats": "\u30B5\u30F3\u30D7\u30EB \u2014 \u30AF\u30A4\u30C3\u30AF\u7D71\u8A08"
    }
  },
  register(ctx) {
    const { module: module2, i18n, ui } = ctx;
    i18n.addTranslations("module." + module2.id, {
      "tabs.sample": "Sample",
      "sections.summary": "Sample Summary",
      "widgets.quickStats": "Sample \u2014 quick stats"
    });
    ui.tabs.register({
      area: "profile.tabs",
      id: "sample",
      label: { namespace: "module." + module2.id, key: "tabs.sample" },
      icon: "\u{1F4E6}",
      position: 20,
      module: module2.id,
      component: SampleTab
    });
    ui.sections.register({
      area: "profile.sections",
      id: "sample-summary",
      label: { namespace: "module." + module2.id, key: "sections.summary" },
      position: 30,
      module: module2.id,
      component: SampleSummary
    });
    ui.sections.register({
      area: "dashboard.widgets",
      id: "sample-stats",
      label: { namespace: "module." + module2.id, key: "widgets.quickStats" },
      position: 10,
      module: module2.id,
      component: SampleWidget
    });
  }
};
var module_default = module;
export {
  module_default as default
};
//# sourceMappingURL=sample.module.js.map
