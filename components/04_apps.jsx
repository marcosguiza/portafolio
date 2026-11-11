/* ---------- Terminal Apps scene ---------- */

const AppGlyph = ({ id, active = false, size = 24, accent, accentSoft }) => {
  const stroke = active ? accent : COLORS.textMut;
  const soft = active ? accentSoft : COLORS.bgAlt;

  const common = {
    width: size,
    height: size,
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg",
    style: { display: "block" }
  };

  switch (id) {
    case "arc":
      return (
        <svg {...common}>
          <rect x="4" y="5" width="16" height="14" rx="3" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M8 12h8M12 8v8" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
          <circle cx="8" cy="8" r="1" fill={stroke} />
          <circle cx="16" cy="16" r="1" fill={stroke} />
        </svg>
      );

    case "avl":
      return (
        <svg {...common}>
          <circle cx="12" cy="12" r="9" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M12 7.4a3.1 3.1 0 0 1 3.1 3.1c0 2.2-3.1 6.1-3.1 6.1s-3.1-3.9-3.1-6.1A3.1 3.1 0 0 1 12 7.4Z" stroke={stroke} strokeWidth="1.6" />
          <circle cx="12" cy="10.5" r="1.1" fill={stroke} />
        </svg>
      );

    case "garmin_dispatch":
      return (
        <svg {...common}>
          <rect x="4" y="5" width="16" height="14" rx="3" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M8 10h8M8 13h5" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
          <path d="M16.8 7.4h.01" stroke={stroke} strokeWidth="2.2" strokeLinecap="round" />
        </svg>
      );

    case "garmin_api":
      return (
        <svg {...common}>
          <rect x="4" y="6" width="6" height="12" rx="2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <rect x="14" y="6" width="6" height="12" rx="2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M10 9h4M10 15h4" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
          <circle cx="7" cy="12" r="1" fill={stroke} />
          <circle cx="17" cy="12" r="1" fill={stroke} />
        </svg>
      );

    case "mail":
      return (
        <svg {...common}>
          <rect x="4" y="6" width="16" height="12" rx="2.5" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M6.5 8.5 12 13l5.5-4.5" stroke={stroke} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      );

    case "sensors":
      return (
        <svg {...common}>
          <circle cx="6.5" cy="12" r="2.2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <circle cx="17.5" cy="8" r="2.2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <circle cx="17.5" cy="16" r="2.2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M8.7 11.3 15.2 8.8M8.7 12.7l6.5 2.5" stroke={stroke} strokeWidth="1.5" strokeLinecap="round" />
        </svg>
      );

    case "rtd":
      return (
        <svg {...common}>
          <rect x="4" y="7" width="7" height="10" rx="2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <rect x="13" y="7" width="7" height="10" rx="2" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M11 12h2" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
          <circle cx="7.5" cy="12" r="1" fill={stroke} />
          <circle cx="16.5" cy="12" r="1" fill={stroke} />
        </svg>
      );

    case "vms":
      return (
        <svg {...common}>
          <path d="M4.5 15.5c2.4-1.7 4.8-2.6 7.2-2.6 2.4 0 4.9.9 7.8 2.6" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
          <path d="M7 13V9.6c0-.9.7-1.6 1.6-1.6h5c.9 0 1.6.7 1.6 1.6V13" stroke={stroke} strokeWidth="1.4" />
          <path d="M10.2 7.9 12 5.8l1.8 2.1" stroke={stroke} strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round" />
          <circle cx="12" cy="17.2" r="1" fill={stroke} />
        </svg>
      );

    case "heavy_can_bus":
      return (
        <svg {...common}>
          <rect x="4" y="6" width="16" height="12" rx="3" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M8 10h8M8 14h4" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
          <path d="M7 6V4M11 6V4M15 6V4M17 20v-2M13 20v-2M9 20v-2" stroke={stroke} strokeWidth="1.2" strokeLinecap="round" />
        </svg>
      );

    case "modbus":
      return (
        <svg {...common}>
          <rect x="3.8" y="6" width="5.2" height="12" rx="1.6" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <rect x="14.8" y="6" width="5.2" height="12" rx="1.6" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M9.4 10h5.2M9.4 14h5.2" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
        </svg>
      );

    default:
      return (
        <svg {...common}>
          <circle cx="12" cy="12" r="9" fill={soft} stroke={stroke} strokeWidth="1.2" />
          <path d="M8 12h8M12 8v8" stroke={stroke} strokeWidth="1.6" strokeLinecap="round" />
        </svg>
      );
  }
};

const AppsScene = ({ setScene, setActiveProduct }) => {
  const lang = useLang();
  const D = window[BRAND.dataKey];

  const apps = Array.isArray(D?.terminalApps) ? D.terminalApps : [];
  const products = Array.isArray(D?.products) ? D.products : [];

  const [activeApp, setActiveApp] = useState(apps[0]?.id ?? null);
  const [vw, setVw] = useState(typeof window !== "undefined" ? window.innerWidth : 1440);

  useEffect(() => {
    const onResize = () => setVw(window.innerWidth);
    window.addEventListener("resize", onResize);
    return () => window.removeEventListener("resize", onResize);
  }, []);

  useEffect(() => {
    if (!apps.length) {
      setActiveApp(null);
      return;
    }
    if (!apps.some(a => a.id === activeApp)) {
      setActiveApp(apps[0].id);
    }
  }, [apps, activeApp]);

  const mobile = vw < 980;

  const accent = BRAND.brand === "viachain" ? COLORS.accents : COLORS.brand;
  const accentSoft = BRAND.brand === "viachain" ? COLORS.ctaGhost : COLORS.brandSoft;

  const app = apps.find(a => a.id === activeApp) ?? apps[0] ?? null;
  const compatible = app?.devices
    ? app.devices.map(id => products.find(p => p.id === id)).filter(Boolean)
    : [];

  const stickyTop = 108;
  const panelHeight = `calc(100vh - ${stickyTop + 28}px)`;

  if (!app) {
    return (
      <div style={{ minHeight: "100vh", paddingTop: 88, paddingBottom: 72, background: COLORS.bg }}>
        <div style={{ maxWidth: 1280, margin: "0 auto", padding: mobile ? "0 16px" : "0 28px" }}>
          <SectionNum
            n={3}
            label={t(D?.nav?.apps || { es: "Apps", en: "Apps", pt: "Apps" }, lang)}
          />
          <h2 style={{ ...h2Style, marginBottom: 18 }}>
            {t({ es: "Terminal Apps", en: "Terminal Apps", pt: "Terminal Apps" }, lang)}
          </h2>
          <p
            style={{
              fontFamily: FONTSANS,
              fontSize: 15,
              color: COLORS.textMut,
              lineHeight: 1.7
            }}
          >
            {t({
              es: "No hay aplicaciones disponibles en esta versión del portafolio.",
              en: "No applications are available in this version of the portfolio.",
              pt: "Não há aplicações disponíveis nesta versão do portfólio."
            }, lang)}
          </p>
        </div>
      </div>
    );
  }

  return (
    <div style={{ minHeight: "100vh", paddingTop: 88, paddingBottom: 72, background: COLORS.bg }}>
      <div style={{ maxWidth: 1280, margin: "0 auto", padding: mobile ? "0 16px" : "0 28px" }}>
        <SectionNum n={3} label={t(D.nav.apps, lang)} />

        <h2 style={{ ...h2Style, marginBottom: 18 }}>
          {t({
            es: ["Aplicaciones listas", "para integrarse."],
            en: ["Applications ready", "to integrate."],
            pt: ["Aplicações prontas", "para integrar."]
          }, lang).map((line, i) => (
            <div
              key={i}
              style={{
                fontStyle: i === 1 ? "italic" : "normal",
                color: i === 1 ? COLORS.textMut : COLORS.text
              }}
            >
              {line}
            </div>
          ))}
        </h2>

        <p
          style={{
            fontFamily: FONTSANS,
            fontSize: 16,
            color: COLORS.textMut,
            maxWidth: 760,
            margin: "0 0 34px",
            lineHeight: 1.7
          }}
        >
          {t({
            es: "Terminal Apps son aplicaciones Lua preinstaladas y configurables que amplían la funcionalidad de los terminales programables para escenarios específicos de operación.",
            en: "Terminal Apps are pre-installed, configurable Lua applications that extend the functionality of programmable terminals for specific operating scenarios.",
            pt: "Terminal Apps são aplicações Lua pré-instaladas e configuráveis que ampliam a funcionalidade dos terminais programáveis para cenários operacionais específicos."
          }, lang)}
        </p>

        <div
          style={{
            display: "grid",
            gridTemplateColumns: mobile ? "1fr" : "340px minmax(0,1fr)",
            gap: mobile ? 20 : 24,
            alignItems: "start"
          }}
        >
          <div
            style={{
              background: COLORS.panel,
              border: `1px solid ${COLORS.line || COLORS.lines}`,
              borderRadius: 20,
              padding: mobile ? 18 : 18,
              position: mobile ? "relative" : "sticky",
              top: mobile ? "auto" : stickyTop,
              maxHeight: mobile ? "none" : panelHeight,
              overflow: "hidden",
              alignSelf: "start"
            }}
          >
            <div
              style={{
                fontFamily: FONTMONO,
                fontSize: 10,
                letterSpacing: 1.4,
                color: COLORS.textMut,
                textTransform: "uppercase",
                marginBottom: 12,
                paddingBottom: 12,
                borderBottom: `1px solid ${COLORS.line || COLORS.lines}`
              }}
            >
              {t({ es: "Aplicaciones", en: "Applications", pt: "Aplicações" }, lang)} · {apps.length}
            </div>

            <div
              style={{
                display: "grid",
                gap: 8,
                overflowY: mobile ? "visible" : "auto",
                maxHeight: mobile ? "none" : `calc(${panelHeight} - 48px)`,
                paddingRight: mobile ? 0 : 4
              }}
            >
              {apps.map((item, i) => {
                const active = item.id === app.id;

                return (
                  <button
                    key={item.id}
                    onClick={() => setActiveApp(item.id)}
                    style={{
                      width: "100%",
                      padding: "14px 14px 13px",
                      borderRadius: 14,
                      border: `1px solid ${active ? accent : (COLORS.line || COLORS.lines)}`,
                      background: active ? accentSoft : COLORS.panel,
                      textAlign: "left",
                      cursor: "pointer",
                      transition: "all .18s ease"
                    }}
                  >
                    <div
                      style={{
                        display: "grid",
                        gridTemplateColumns: "28px 1fr auto",
                        gap: 10,
                        alignItems: "start"
                      }}
                    >
                      <span
                        style={{
                          fontFamily: FONTMONO,
                          fontSize: 10,
                          color: COLORS.textDim,
                          letterSpacing: 1,
                          paddingTop: 2
                        }}
                      >
                        {String(i + 1).padStart(2, "0")}
                      </span>

                      <div style={{ display: "grid", gap: 3 }}>
                        <div
                          style={{
                            fontFamily: FONTDISPLAY,
                            fontSize: 20,
                            color: active ? COLORS.text : COLORS.textMut,
                            letterSpacing: -0.4,
                            lineHeight: 1.05
                          }}
                        >
                          {item.name}
                        </div>

                        <div
                          style={{
                            fontFamily: FONTSANS,
                            fontSize: 12,
                            color: COLORS.textMut,
                            lineHeight: 1.45
                          }}
                        >
                          {t(item.t, lang)}
                        </div>
                      </div>

                      <div
                        style={{
                          width: 34,
                          height: 34,
                          borderRadius: 10,
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          background: active ? accentSoft : COLORS.bgAlt,
                          border: `1px solid ${active ? accent : (COLORS.line || COLORS.lines)}`
                        }}
                      >
                        <AppGlyph
                          id={item.id}
                          active={active}
                          size={18}
                          accent={accent}
                          accentSoft={accentSoft}
                        />
                      </div>
                    </div>
                  </button>
                );
              })}
            </div>
          </div>

          <div
            style={{
              position: mobile ? "static" : "sticky",
              top: mobile ? "auto" : stickyTop,
              maxHeight: mobile ? "none" : panelHeight,
              overflowY: mobile ? "visible" : "auto",
              paddingRight: mobile ? 0 : 4,
              alignSelf: "start"
            }}
          >
            <div
              style={{
                background: COLORS.panel,
                border: `1px solid ${COLORS.line || COLORS.lines}`,
                borderRadius: 20,
                padding: mobile ? 18 : 26,
                marginBottom: 18,
                overflow: "hidden"
              }}
            >
              <Eyebrow>
                {t({
                  es: "Aplicación seleccionada",
                  en: "Selected application",
                  pt: "Aplicação selecionada"
                }, lang)}
              </Eyebrow>

              <div
                style={{
                  display: "grid",
                  gridTemplateColumns: mobile ? "1fr" : "84px minmax(0,1fr)",
                  gap: 18,
                  alignItems: "start",
                  marginTop: 18
                }}
              >
                <div
                  style={{
                    width: mobile ? 64 : 76,
                    height: mobile ? 64 : 76,
                    borderRadius: 18,
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    background: `radial-gradient(circle at 30% 30%, ${accentSoft}, ${COLORS.panel})`,
                    border: `1px solid ${accent}`
                  }}
                >
                  <AppGlyph
                    id={app.id}
                    active
                    size={mobile ? 30 : 36}
                    accent={accent}
                    accentSoft={accentSoft}
                  />
                </div>

                <div>
                  <div
                    style={{
                      display: "flex",
                      alignItems: "baseline",
                      gap: 14,
                      flexWrap: "wrap",
                      marginBottom: 8
                    }}
                  >
                    <div
                      style={{
                        fontFamily: FONTDISPLAY,
                        fontSize: mobile ? 34 : 52,
                        color: COLORS.text,
                        letterSpacing: -1
                      }}
                    >
                      {app.name}
                    </div>

                    <div
                      style={{
                        fontFamily: FONTDISPLAY,
                        fontStyle: "italic",
                        fontSize: mobile ? 18 : 22,
                        color: COLORS.textMut
                      }}
                    >
                      {t(app.t, lang)}
                    </div>
                  </div>

                  <p
                    style={{
                      fontFamily: FONTSANS,
                      fontSize: 15,
                      color: COLORS.text,
                      margin: 0,
                      maxWidth: 760,
                      lineHeight: 1.7
                    }}
                  >
                    {t(app.d, lang)}
                  </p>
                </div>
              </div>
            </div>

            <div
              style={{
                background: COLORS.panel,
                border: `1px solid ${COLORS.line || COLORS.lines}`,
                borderRadius: 20,
                padding: mobile ? 18 : 24
              }}
            >
              <div
                style={{
                  fontFamily: FONTMONO,
                  fontSize: 10,
                  letterSpacing: 1.5,
                  color: COLORS.textMut,
                  textTransform: "uppercase",
                  marginBottom: 16
                }}
              >
                {t({ es: "Compatible con", en: "Compatible with", pt: "Compatível com" }, lang)} · {compatible.length}
              </div>

              <div
                style={{
                  display: "grid",
                  gridTemplateColumns: mobile ? "1fr" : "repeat(2, minmax(0,1fr))",
                  gap: 10
                }}
              >
                {compatible.map((p) => (
                  <button
                    key={p.id}
                    onClick={() => {
                      setActiveProduct(p.id);
                      setScene("showroom");
                    }}
                    style={{
                      background: COLORS.panel,
                      border: `1px solid ${COLORS.line || COLORS.lines}`,
                      borderRadius: 14,
                      padding: 16,
                      cursor: "pointer",
                      textAlign: "left",
                      display: "flex",
                      gap: 14,
                      alignItems: "center",
                      overflow: "hidden",
                      position: "relative"
                    }}
                  >
                    <div
                      style={{
                        width: 70,
                        height: 58,
                        flexShrink: 0,
                        borderRadius: 12,
                        background: `linear-gradient(180deg, ${COLORS.panelHi}, ${COLORS.panel})`,
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        overflow: "hidden"
                      }}
                    >
                      <img
                        src={p.img}
                        alt=""
                        style={{ width: 76, height: 76, objectFit: "contain" }}
                      />
                    </div>

                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div
                        style={{
                          fontFamily: FONTDISPLAY,
                          fontSize: 20,
                          color: COLORS.text,
                          lineHeight: 1.05,
                          marginBottom: 4
                        }}
                      >
                        {p.name}
                      </div>

                      <div
                        style={{
                          fontFamily: FONTMONO,
                          fontSize: 10,
                          letterSpacing: 1,
                          color: COLORS.textMut,
                          textTransform: "uppercase"
                        }}
                      >
                        {t(p.category, lang)}
                      </div>
                    </div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

Object.assign(window, { AppsScene });
