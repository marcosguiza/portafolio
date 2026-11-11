// ---------- Showroom scene: 3D-feel product explorer with hotspots ----------
const ShowroomScene = ({ activeProduct, setActiveProduct, setScene }) => {
  const { lang } = useLang();
  const D = window[BRAND.dataKey];
  const products = D.products;
  const current = products.find(p => p.id === activeProduct) || products[0];
  const [activeHotspot, setActiveHotspot] = useState(null);
  const [tilt, setTilt] = useState({ x: 0, y: 0 });
  const [inStage, setInStage] = useState(false);
  const stageRef = useRef(null);

  const specRefs = {
    satellite: useRef(null), cellular: useRef(null), gnss: useRef(null),
    power: useRef(null), env: useRef(null), certs: useRef(null),
    io: useRef(null), ports: useRef(null), io_16pin: useRef(null),
  };
  const activeKey = activeHotspot !== null && current.hotspots[activeHotspot]
    ? (current.hotspots[activeHotspot].specKey || null)
    : null;
  const hl = (key) => activeKey === key;

  useEffect(() => {
    setActiveHotspot(null);
    setTilt({ x: 0, y: 0 });
    setInStage(false);
  }, [current.id]);

  const [vw, setVw] = useState(typeof window !== 'undefined' ? window.innerWidth : 1400);
  useEffect(() => {
    const on = () => setVw(window.innerWidth);
    window.addEventListener('resize', on);
    return () => window.removeEventListener('resize', on);
  }, []);

  const compact = vw < 1280;
  const narrow = vw < 1024;
  const gridCols = narrow ? '220px 1fr' : (compact ? '240px 1fr 340px' : '280px 1fr 400px');

  return (
    <div style={{ ...(narrow ? { minHeight: '100vh' } : { height: '100vh', overflow: 'hidden' }), paddingTop: 80, background: COLORS.bg }}>
      <div style={{ display: 'grid', gridTemplateColumns: gridCols, ...(narrow ? { minHeight: 'calc(100vh - 80px)' } : { height: 'calc(100vh - 80px)' }) }}>
        <aside style={{ borderRight: `1px solid ${COLORS.line}`, padding: '40px 0', background: COLORS.bgAlt, overflowY: 'auto' }}>
          <div style={{ padding: '0 32px 24px' }}>
            <Eyebrow>{t({ es: "Seleccionar", en: "Select" }, lang)}</Eyebrow>
          </div>
          {products.map((p, i) => {
            const active = p.id === current.id;
            return (
              <button
                key={p.id}
                onClick={() => setActiveProduct(p.id)}
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: 16,
                  width: '100%',
                  padding: '18px 32px',
                  background: active ? COLORS.panel : 'transparent',
                  border: 'none',
                  cursor: 'pointer',
                  textAlign: 'left',
                  borderLeft: `2px solid ${active ? COLORS.brand : 'transparent'}`,
                  borderBottom: `1px solid ${COLORS.line}`,
                  transition: 'background .2s',
                }}
              >
                <div style={{ fontFamily: FONT_MONO, fontSize: 10, color: COLORS.textDim, letterSpacing: 1 }}>
                  {String(i + 1).padStart(2, '0')}
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontFamily: FONT_SERIF, fontSize: 20, color: active ? COLORS.text : COLORS.textMut }}>{p.name}</div>
                  <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 1.2, color: COLORS.textDim, textTransform: 'uppercase', marginTop: 2 }}>
                    {t(p.category, lang)}
                  </div>
                </div>
                {active && <span style={{ color: COLORS.brand, fontFamily: FONT_MONO }}>●</span>}
              </button>
            );
          })}
        </aside>

        <main style={{ position: 'relative', overflow: 'hidden', display: 'flex', flexDirection: 'column' }}>
          <div style={{ padding: compact ? '32px 32px 0' : '40px 60px 0', display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 24 }}>
            <div style={{ minWidth: 0, flex: 1 }}>
              <Eyebrow>{t(current.category, lang)}</Eyebrow>
              <h1 style={{ fontFamily: FONT_SERIF, fontSize: 'clamp(40px, 5.2vw, 72px)', fontWeight: 300, color: COLORS.text, margin: '16px 0 0', letterSpacing: -2, whiteSpace: 'nowrap' }}>
                {current.name}
              </h1>
              <p style={{ fontFamily: FONT_SERIF, fontStyle: 'italic', fontSize: 'clamp(16px, 1.6vw, 22px)', color: COLORS.textMut, margin: '12px 0 0', fontWeight: 300 }}>
                {t(current.hero, lang)}
              </p>
            </div>

            {!compact && (
              <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 1.5, color: COLORS.textDim, textTransform: 'uppercase', textAlign: 'right', whiteSpace: 'nowrap' }}>
                <div>{t({ es: "Mueve el cursor para explorar", en: "Move cursor to explore", pt: "Mova o cursor para explorar" }, lang)}</div>
                <div style={{ marginTop: 4, color: COLORS.brand }}>{t({ es: "Click en los puntos ●", en: "Click the ● hotspots", pt: "Clique nos pontos ●" }, lang)}</div>
              </div>
            )}
          </div>

          <div style={{ flex: 1, position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <StageBG />
            <div
              ref={stageRef}
              onMouseMove={(e) => {
                if (activeHotspot !== null) return;
                const r = stageRef.current.getBoundingClientRect();
                const dx = ((e.clientX - r.left) / r.width - 0.5) * 2;
                const dy = ((e.clientY - r.top) / r.height - 0.5) * 2;
                setTilt({ x: dy * -8, y: dx * 10 });
              }}
              onMouseEnter={() => setInStage(true)}
              onMouseLeave={() => {
                setInStage(false);
                setTilt({ x: 0, y: 0 });
              }}
              style={{ position: 'relative', width: '78%', maxWidth: 640, aspectRatio: '4/3', cursor: 'default' }}
            >
              <div style={{ position: 'absolute', inset: 0, animation: (inStage || activeHotspot !== null) ? 'none' : 'productFloat 3.5s ease-in-out infinite' }}>
                <div
                  style={{
                    position: 'absolute',
                    inset: 0,
                    transform: `perspective(900px) rotateX(${tilt.x}deg) rotateY(${tilt.y}deg)`,
                    transition: inStage ? 'transform 0.08s ease-out' : 'transform 0.7s cubic-bezier(.2,.9,.3,1)',
                    transformStyle: 'preserve-3d',
                  }}
                >
                  <img
                    src={current.img}
                    alt={current.name}
                    style={{
                      width: '100%',
                      height: '100%',
                      objectFit: 'contain',
                      filter: `drop-shadow(${(-tilt.y * 1.5).toFixed(0)}px ${(tilt.x * 1.5 + 38).toFixed(0)}px 52px rgba(0,0,0,0.62)) drop-shadow(${(-tilt.y * 0.6).toFixed(0)}px ${(tilt.x * 0.6 + 14).toFixed(0)}px 18px rgba(0,0,0,0.32))`,
                      transform: activeHotspot !== null ? 'scale(1.18)' : 'scale(1)',
                      transformOrigin: activeHotspot !== null && current.hotspots[activeHotspot]
                        ? `${current.hotspots[activeHotspot].x}% ${current.hotspots[activeHotspot].y}%`
                        : '50% 50%',
                      transition: `filter ${inStage ? '0.08s ease-out' : '0.7s ease-out'}, transform 0.45s cubic-bezier(.2,.9,.3,1)`,
                      pointerEvents: 'none',
                      userSelect: 'none',
                    }}
                    draggable={false}
                  />
                </div>
              </div>

              {current.hotspots.map((h, i) => (
                <Hotspot
                  key={i}
                  h={h}
                  i={i}
                  active={activeHotspot === i}
                  onClick={() => {
                    const next = activeHotspot === i ? null : i;
                    setActiveHotspot(next);
                    if (next !== null) setTilt({ x: 0, y: 0 });
                  }}
                />
              ))}

              <div
                style={{
                  position: 'absolute',
                  left: '10%',
                  right: '10%',
                  bottom: '-4%',
                  height: 30,
                  background: 'radial-gradient(ellipse, rgba(0,0,0,0.4), transparent 70%)',
                  filter: 'blur(8px)',
                  transform: `translateX(${(tilt.y * 0.8).toFixed(0)}px)`,
                  transition: inStage ? 'transform 0.08s ease-out' : 'transform 0.7s ease-out',
                }}
              />
            </div>
          </div>

          <div style={{ padding: compact ? '22px 32px 32px' : '28px 60px 40px', borderTop: `1px solid ${COLORS.line}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 16, flexWrap: 'wrap' }}>
            <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 1.5, color: COLORS.textDim, textTransform: 'uppercase' }}>
              {String(products.indexOf(current) + 1).padStart(2, '0')} / {String(products.length).padStart(2, '0')}
            </div>
            <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
              {['satellite', 'cellular', 'gnss', 'power', 'env']
                .filter(k => !(compact && ['power', 'env'].includes(k)))
                .map(k => (
                  <QuickBadge
                    key={k}
                    label={t({
                      satellite: { es: 'SAT', en: 'SAT' },
                      cellular: { es: 'CEL', en: 'CEL' },
                      gnss: { es: 'GNSS', en: 'GNSS' },
                      power: { es: 'PWR', en: 'PWR' },
                      env: { es: 'ENV', en: 'ENV' },
                    }[k], lang)}
                    value={typeof current[k] === 'object' ? t(current[k], lang) : current[k]}
                  />
                ))}
            </div>
          </div>
        </main>

        {!narrow && (
          <aside style={{ borderLeft: `1px solid ${COLORS.line}`, padding: '40px 28px', background: COLORS.bgAlt, overflowY: 'auto' }}>
            <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 16 }}>
              {t({ es: "Descripción", en: "Description", pt: "Descrição" }, lang)}
            </div>
            <p style={{ fontFamily: FONT_SANS, fontSize: 14, lineHeight: 1.65, color: COLORS.text, margin: '0 0 32px' }}>
              {t(current.description, lang)}
            </p>

            <SpecRow label={t({ es: 'Satelital', en: 'Satellite' }, lang)} value={typeof current.satellite === 'object' ? t(current.satellite, lang) : current.satellite} highlighted={hl('satellite')} specRef={specRefs.satellite} />
            <SpecRow label={t({ es: 'Celular', en: 'Cellular' }, lang)} value={current.cellular} highlighted={hl('cellular')} specRef={specRefs.cellular} />
            <SpecRow label="GNSS" value={typeof current.gnss === 'object' ? t(current.gnss, lang) : current.gnss} highlighted={hl('gnss')} specRef={specRefs.gnss} />
            <SpecRow label={t({ es: 'Energía', en: 'Power' }, lang)} value={typeof current.power === 'object' ? t(current.power, lang) : current.power} highlighted={hl('power')} specRef={specRefs.power} />
            <SpecRow label={t({ es: 'Ambiente', en: 'Environment' }, lang)} value={current.env} highlighted={hl('env')} specRef={specRefs.env} />
            {current.dims && <SpecRow label={t({ es: 'Dimensiones', en: 'Dimensions' }, lang)} value={current.dims} />}
            {current.sku && <SpecRow label="SKU" value={current.sku} />}
            {current.memory && <SpecRow label={t({ es: 'Memoria', en: 'Memory' }, lang)} value={current.memory} />}
            <SpecRow label={t({ es: 'Programación', en: 'Programming' }, lang)} value={typeof current.prog === 'object' ? t(current.prog, lang) : current.prog} />
            {current.backup && <SpecRow label={t({ es: 'Respaldo', en: 'Backup' }, lang)} value={t({ es: 'Sí', en: 'Yes', pt: 'Sim' }, lang)} />}
            {current.certs && <SpecRow label="Certs" value={current.certs} highlighted={hl('certs')} specRef={specRefs.certs} />}

            {Array.isArray(current.io) && current.io.length > 0 && (
              <div
                ref={specRefs.io}
                className={hl('io') ? 'spec-highlighted' : undefined}
                style={{ marginTop: 32, paddingTop: 24, borderTop: `1px solid ${COLORS.line}` }}
              >
                <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: hl('io') ? COLORS.brand : COLORS.textMut, textTransform: 'uppercase', marginBottom: 12, transition: 'color .3s' }}>
                  I/O
                </div>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                  {current.io.map((x, i) => (
                    <span key={i} style={tagStyle}>{x}</span>
                  ))}
                </div>
              </div>
            )}

            <div
              ref={specRefs.ports}
              className={hl('ports') ? 'spec-highlighted' : undefined}
              style={{ marginTop: 24 }}
            >
              <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: hl('ports') ? COLORS.brand : COLORS.textMut, textTransform: 'uppercase', marginBottom: 12, transition: 'color .3s' }}>
                {t({ es: 'Puertos', en: 'Ports', pt: 'Portas' }, lang)}
              </div>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                {(Array.isArray(current.ports) ? current.ports : []).map((x, i) => (
                  <span key={i} style={tagStyle}>{x}</span>
                ))}
              </div>
            </div>

            {(() => {
              const io16 = Array.isArray(current.io_16pin) ? current.io_16pin : [];
              const p16 = Array.isArray(current.ports_16pin) ? current.ports_16pin : [];
              if (!io16.length && !p16.length) return null;
              const isHl = hl('io_16pin');
              return (
                <div
                  ref={specRefs.io_16pin}
                  className={isHl ? 'spec-highlighted' : undefined}
                  style={{ marginTop: 24, paddingTop: 20, borderTop: `1px dashed ${COLORS.line}` }}
                >
                  <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 1.5, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 10 }}>
                    + {t({ es: 'Conector 16-pin (opcional)', en: '16-pin connector (optional)', pt: 'Conector 16-pin (opcional)' }, lang)}
                  </div>
                  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                    {[...io16, ...p16].map((x, i) => (
                      <span key={i} style={{ ...tagStyle, borderStyle: 'dashed', color: COLORS.textMut }}>{x}</span>
                    ))}
                  </div>
                </div>
              );
            })()}

            {Array.isArray(current.bundles) && current.bundles.length > 0 && (
              <div style={{ marginTop: 32, paddingTop: 24, borderTop: `1px solid ${COLORS.line}` }}>
                <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 12 }}>
                  {t({ es: 'Variantes de pedido', en: 'Ordering variants', pt: 'Variantes de pedido' }, lang)}
                </div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                  {current.bundles.map((k, i) => (
                    <div key={i} style={{ display: 'flex', gap: 10, alignItems: 'baseline' }}>
                      <span style={{ fontFamily: FONT_MONO, fontSize: 10, color: COLORS.brand, flexShrink: 0, letterSpacing: 0.5 }}>{k.sku}</span>
                      <span style={{ fontFamily: FONT_SANS, fontSize: 12, color: COLORS.textMut, lineHeight: 1.4 }}>{k.name}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {Array.isArray(current.kits) && current.kits.length > 0 && (
              <div style={{ marginTop: 32, paddingTop: 24, borderTop: `1px solid ${COLORS.line}` }}>
                <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 12 }}>
                  {t({ es: 'Cables & Kits', en: 'Cables & Kits', pt: 'Cabos & Kits' }, lang)}
                </div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                  {current.kits.map((k, i) => (
                    <div key={i} style={{ display: 'flex', gap: 10, alignItems: 'baseline' }}>
                      <span style={{ fontFamily: FONT_MONO, fontSize: 10, color: COLORS.brand, flexShrink: 0, letterSpacing: 0.5 }}>{k.sku}</span>
                      <span style={{ fontFamily: FONT_SANS, fontSize: 12, color: COLORS.textMut, lineHeight: 1.4 }}>{k.name}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}
            <ResourcesBlock productId={current.id} />
            <CompatibleAccessories productName={current.name} setScene={setScene} />
          </aside>
        )}
      </div>

      {narrow && (
        <aside style={{ borderTop: `1px solid ${COLORS.line}`, padding: '32px', background: COLORS.bgAlt }}>
          <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 16 }}>
            {t({ es: "Descripción", en: "Description", pt: "Descrição" }, lang)}
          </div>
          <p style={{ fontFamily: FONT_SANS, fontSize: 14, lineHeight: 1.65, color: COLORS.text, margin: '0 0 24px', maxWidth: 700 }}>
            {t(current.description, lang)}
          </p>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 0 }}>
            <SpecRow label={t({ es: 'Satelital', en: 'Satellite', pt: 'Satelital' }, lang)} value={current.satellite} />
            <SpecRow label={t({ es: 'Celular', en: 'Cellular', pt: 'Celular' }, lang)} value={current.cellular} />
            <SpecRow label={t({ es: 'Energía', en: 'Power', pt: 'Energia' }, lang)} value={typeof current.power === 'object' ? t(current.power, lang) : current.power} />
            <SpecRow label={t({ es: 'Ambiente', en: 'Environment', pt: 'Ambiente' }, lang)} value={current.env} />
          </div>
          <ResourcesBlock productId={current.id} />
          <CompatibleAccessories productName={current.name} setScene={setScene} />
        </aside>
      )}
    </div>
  );
};

const CompatibleAccessories = ({ productName, setScene }) => {
  const { lang } = useLang();
  const D = window[BRAND.dataKey];
  const accs = (D.accessories || []).filter(acc =>
    acc.featured === true &&
    (acc.compatible || []).some(c => c === productName || c.startsWith(productName))
  );
  if (!accs.length) return null;

  return (
    <div style={{ marginTop: 32, paddingTop: 24, borderTop: `1px solid ${COLORS.line}` }}>
      <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 14 }}>
        {t({ es: 'Accesorios compatibles', en: 'Compatible accessories', pt: 'Acessórios compatíveis' }, lang)}
      </div>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        {accs.map(acc => (
          <button
            key={acc.id}
            onClick={() => setScene && setScene('accessories')}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 12,
              padding: '10px 14px',
              background: COLORS.panel,
              border: `1px solid ${COLORS.line}`,
              borderRadius: 10,
              cursor: 'pointer',
              textAlign: 'left',
              transition: 'border-color .15s',
              width: '100%',
            }}
            onMouseEnter={e => { e.currentTarget.style.borderColor = COLORS.brand; }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = COLORS.line; }}
          >
            <img
              src={acc.img}
              alt=""
              style={{ width: 40, height: 40, objectFit: 'contain', flexShrink: 0, borderRadius: 6, background: COLORS.bg, padding: 2 }}
            />
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontFamily: FONT_SANS, fontSize: 13, fontWeight: 500, color: COLORS.text, lineHeight: 1.3 }}>
                {acc.name}
              </div>
              <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 1, color: COLORS.textMut, textTransform: 'uppercase', marginTop: 3, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {t(acc.category, lang)}
              </div>
              {acc.connector16pin && (
                <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 0.5, color: COLORS.brand, marginTop: 3 }}>
                  + {t({ es: 'requiere conector 16-pin', en: '16-pin connector required', pt: 'requer conector 16 pinos' }, lang)}
                </div>
              )}
            </div>
            <span style={{ fontFamily: FONT_MONO, fontSize: 12, color: COLORS.textMut, flexShrink: 0 }}>→</span>
          </button>
        ))}
      </div>
    </div>
  );
};

const tagStyle = {
  fontFamily: FONT_MONO,
  fontSize: 11,
  padding: '5px 10px',
  background: COLORS.panel,
  color: COLORS.textMut,
  border: `1px solid ${COLORS.line}`,
  letterSpacing: 0.3,
  borderRadius: 6,
};

const SpecRow = ({ label, value, highlighted, specRef }) => {
  useEffect(() => {
    if (highlighted && specRef && specRef.current) {
      specRef.current.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }, [highlighted]);

  if (!value || value === '—') return null;

  return (
    <div
      ref={specRef}
      className={highlighted ? 'spec-highlighted' : undefined}
      style={{ display: 'grid', gridTemplateColumns: '100px 1fr', gap: 16, padding: '10px 4px', borderBottom: `1px solid ${COLORS.line}` }}
    >
      <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 1.5, color: highlighted ? COLORS.brand : COLORS.textMut, textTransform: 'uppercase', paddingTop: 2, transition: 'color .3s' }}>
        {label}
      </div>
      <div style={{ fontFamily: FONT_SANS, fontSize: 13, color: COLORS.text, lineHeight: 1.45 }}>
        {value}
      </div>
    </div>
  );
};

const QuickBadge = ({ label, value }) => (
  <div style={{ display: 'flex', flexDirection: 'column', minWidth: 0 }}>
    <div style={{ fontFamily: FONT_MONO, fontSize: 9, color: COLORS.textDim, letterSpacing: 1.2 }}>{label}</div>
    <div style={{ fontFamily: FONT_MONO, fontSize: 11, color: COLORS.text, marginTop: 2, maxWidth: 160, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
      {value || '—'}
    </div>
  </div>
);

const Hotspot = ({ h, i, active, onClick }) => {
  const { lang } = useLang();
  return (
    <>
      <button
        onClick={onClick}
        style={{
          position: 'absolute',
          left: `${h.x}%`,
          top: `${h.y}%`,
          transform: 'translate(-50%, -50%)',
          width: 28,
          height: 28,
          borderRadius: '50%',
          background: 'transparent',
          border: 'none',
          cursor: 'pointer',
          padding: 0,
          zIndex: 10,
        }}
      >
        <span
          style={{
            position: 'absolute',
            inset: 0,
            borderRadius: '50%',
            border: `1px solid ${COLORS.brand}`,
            animation: 'pulse 2s ease-out infinite',
            opacity: active ? 0 : 1,
          }}
        />
        <span
          style={{
            position: 'absolute',
            left: '50%',
            top: '50%',
            transform: 'translate(-50%,-50%)',
            width: active ? 14 : 10,
            height: active ? 14 : 10,
            borderRadius: '50%',
            background: COLORS.brand,
            boxShadow: '0 0 0 3px rgba(227,6,19,0.22)',
            transition: 'all .2s',
          }}
        />
      </button>
      {active && (
        <div
          style={{
            position: 'absolute',
            left: `${h.x > 50 ? h.x - 2 : h.x + 2}%`,
            top: `${h.y}%`,
            transform: `translate(${h.x > 50 ? '-100%' : '0'}, -50%)`,
            marginLeft: h.x > 50 ? -18 : 18,
            zIndex: 11,
            background: COLORS.panel,
            border: `1px solid ${COLORS.lineHi}`,
            padding: '12px 16px',
            maxWidth: 240,
            minWidth: 180,
            fontFamily: FONT_SANS,
            fontSize: 12,
            lineHeight: 1.45,
            color: COLORS.text,
            borderRadius: 10,
            boxShadow: '0 4px 16px rgba(0,0,0,0.08)',
          }}
        >
          <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 1.5, color: COLORS.brand, marginBottom: 6, textTransform: 'uppercase' }}>
            / {String(i + 1).padStart(2, '0')}
          </div>
          {t(h.t, lang)}
        </div>
      )}
    </>
  );
};

const StageBG = () => (
  <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none' }}>
    <svg viewBox="0 0 1000 700" preserveAspectRatio="xMidYMid slice" style={{ position: 'absolute', inset: 0, width: '100%', height: '100%' }}>
      <defs>
        <radialGradient id="stageGlow" cx="0.5" cy="0.55" r="0.45">
          <stop offset="0%" stopColor={COLORS.signal} stopOpacity="0.08" />
          <stop offset="100%" stopColor={COLORS.signal} stopOpacity="0" />
        </radialGradient>
      </defs>
      <rect width="1000" height="700" fill="url(#stageGlow)" />
      <g fill="none" stroke={COLORS.line} opacity="0.6">
        <ellipse cx="500" cy="500" rx="420" ry="60" />
        <ellipse cx="500" cy="500" rx="300" ry="42" />
        <ellipse cx="500" cy="500" rx="180" ry="26" />
      </g>
    </svg>
  </div>
);

// ---------- PDF viewer modal ----------
const PdfModal = ({ url, title, onClose }) => {
  useEffect(() => {
    const fn = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', fn);
    return () => document.removeEventListener('keydown', fn);
  }, [onClose]);

  return (
    <div
      onClick={onClose}
      style={{
        position: 'fixed',
        inset: 0,
        zIndex: 500,
        background: 'rgba(13,10,7,0.72)',
        backdropFilter: 'blur(6px)',
        WebkitBackdropFilter: 'blur(6px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '24px',
      }}
    >
      <div
        onClick={e => e.stopPropagation()}
        style={{
          position: 'relative',
          width: '100%',
          maxWidth: 900,
          height: '90vh',
          background: COLORS.panel,
          borderRadius: 16,
          overflow: 'hidden',
          display: 'flex',
          flexDirection: 'column',
          boxShadow: '0 32px 80px rgba(0,0,0,0.45)',
        }}
      >
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            padding: '14px 20px',
            borderBottom: `1px solid ${COLORS.line}`,
            background: COLORS.bgAlt,
            flexShrink: 0,
          }}
        >
          <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 1.5, color: COLORS.textMut, textTransform: 'uppercase' }}>
            {title}
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <a
              href={url}
              target="_blank"
              rel="noopener noreferrer"
              title="Abrir en nueva pestaña"
              style={{
                background: 'none',
                border: `1px solid ${COLORS.line}`,
                borderRadius: 8,
                width: 32,
                height: 32,
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: COLORS.textMut,
                fontSize: 13,
                lineHeight: 1,
                textDecoration: 'none',
              }}
            >
              ↗
            </a>
            <button
              onClick={onClose}
              style={{
                background: 'none',
                border: `1px solid ${COLORS.line}`,
                borderRadius: 8,
                width: 32,
                height: 32,
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: COLORS.text,
                fontSize: 16,
                lineHeight: 1,
              }}
            >
              ✕
            </button>
          </div>
        </div>
        <embed
          src={`${url}#toolbar=1&navpanes=1&scrollbar=1&view=FitH`}
          type="application/pdf"
          style={{ flex: 1, width: '100%', border: 'none', display: 'block' }}
        />
      </div>
    </div>
  );
};

// ---------- Resources block (datasheet + hardware guide + install guide) ----------
const DOCS_SESSION_KEY = 'pf_docs_unlocked';

const DocButton = ({ icon, label, fileName, onClick }) => (
  <button
    onClick={onClick}
    style={{
      display: 'grid',
      gridTemplateColumns: '36px 1fr 20px',
      gap: 14,
      alignItems: 'center',
      padding: '16px 18px',
      width: '100%',
      border: `1px solid ${COLORS.line}`,
      background: COLORS.panel,
      cursor: 'pointer',
      textAlign: 'left',
      transition: 'border-color .15s',
      borderRadius: 10,
    }}
    onMouseEnter={e => { e.currentTarget.style.borderColor = COLORS.brand; }}
    onMouseLeave={e => { e.currentTarget.style.borderColor = COLORS.line; }}
  >
    <span style={{ fontFamily: FONT_MONO, fontSize: 18, color: COLORS.brand, textAlign: 'center', lineHeight: 1 }}>
      {icon}
    </span>
    <div style={{ minWidth: 0 }}>
      <div style={{ fontFamily: FONT_SANS, fontSize: 14, color: COLORS.text, lineHeight: 1.3, fontWeight: 500 }}>
        {label}
      </div>
      <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 1, color: COLORS.textMut, textTransform: 'uppercase', marginTop: 4, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
        {fileName}
      </div>
    </div>
    <span style={{ fontFamily: FONT_MONO, fontSize: 12, color: COLORS.textMut }}>→</span>
  </button>
);

const LockedDocButton = ({ label, onClick, lang }) => (
  <button
    onClick={onClick}
    style={{
      display: 'grid',
      gridTemplateColumns: '36px 1fr 20px',
      gap: 14,
      alignItems: 'center',
      padding: '16px 18px',
      width: '100%',
      border: `1px solid ${COLORS.line}`,
      background: COLORS.bg,
      cursor: 'pointer',
      textAlign: 'left',
      transition: 'border-color .15s, opacity .15s',
      borderRadius: 10,
      opacity: 0.72,
    }}
    onMouseEnter={e => { e.currentTarget.style.borderColor = COLORS.lineHi; e.currentTarget.style.opacity = '1'; }}
    onMouseLeave={e => { e.currentTarget.style.borderColor = COLORS.line; e.currentTarget.style.opacity = '0.72'; }}
  >
    <span style={{ fontFamily: FONT_MONO, fontSize: 18, color: COLORS.textDim, textAlign: 'center', lineHeight: 1 }}>
      &#128274;
    </span>
    <div style={{ minWidth: 0 }}>
      <div style={{ fontFamily: FONT_SANS, fontSize: 14, color: COLORS.textMut, lineHeight: 1.3, fontWeight: 500 }}>
        {label}
      </div>
      <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 1, color: COLORS.textDim, textTransform: 'uppercase', marginTop: 4 }}>
        {t({ es: 'Requiere código de acceso', en: 'Requires access code', pt: 'Requer código de acesso' }, lang)}
      </div>
    </div>
    <span style={{ fontFamily: FONT_MONO, fontSize: 12, color: COLORS.textDim }}>→</span>
  </button>
);

const DocAccessModal = ({ onClose, onUnlock, lang }) => {
  const [code, setCode] = useState('');
  const [error, setError] = useState(false);
  const [loading, setLoading] = useState(false);
  const inputRef = useRef(null);

  useEffect(() => {
    const fn = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', fn);
    if (inputRef.current) inputRef.current.focus();
    return () => document.removeEventListener('keydown', fn);
  }, [onClose]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!code.trim() || loading) return;
    setLoading(true);
    setError(false);
    try {
      const res = await fetch('/docs_unlock.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: code.trim() }),
        credentials: 'same-origin',
      });
      const data = await res.json();
      if (data.ok) {
        sessionStorage.setItem(DOCS_SESSION_KEY, '1');
        onUnlock();
      } else {
        setError(true);
        setCode('');
        if (inputRef.current) inputRef.current.focus();
      }
    } catch (_) {
      setError(true);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      onClick={onClose}
      style={{
        position: 'fixed', inset: 0, zIndex: 500,
        background: 'rgba(13,10,7,0.72)',
        backdropFilter: 'blur(6px)', WebkitBackdropFilter: 'blur(6px)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        padding: '24px',
      }}
    >
      <div
        onClick={e => e.stopPropagation()}
        style={{
          width: '100%', maxWidth: 380,
          background: COLORS.panel,
          border: `1px solid ${COLORS.lineHi}`,
          borderRadius: 16,
          padding: '32px 28px',
          boxShadow: '0 32px 80px rgba(0,0,0,0.45)',
        }}
      >
        <div style={{ fontFamily: FONT_MONO, fontSize: 9, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 20 }}>
          {t({ es: 'Acceso restringido', en: 'Restricted access', pt: 'Acesso restrito' }, lang)}
        </div>
        <div style={{ fontFamily: FONT_SERIF, fontSize: 22, fontWeight: 300, color: COLORS.text, marginBottom: 8, letterSpacing: -0.5 }}>
          {t({ es: 'Código de acceso', en: 'Access code', pt: 'Código de acesso' }, lang)}
        </div>
        <p style={{ fontFamily: FONT_SANS, fontSize: 13, color: COLORS.textMut, lineHeight: 1.55, marginBottom: 24 }}>
          {t({
            es: 'Hardware Guides y Guías de instalación están disponibles para socios y distribuidores autorizados.',
            en: 'Hardware Guides and Installation Guides are available for authorized partners and distributors.',
            pt: 'Hardware Guides e Guias de instalação estão disponíveis para parceiros e distribuidores autorizados.',
          }, lang)}
        </p>
        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          <input
            ref={inputRef}
            type="password"
            value={code}
            onChange={e => { setCode(e.target.value); setError(false); }}
            placeholder={t({ es: 'Ingresa el código', en: 'Enter code', pt: 'Insira o código' }, lang)}
            autoComplete="off"
            style={{
              background: COLORS.bg,
              border: `1px solid ${error ? COLORS.brand : COLORS.line}`,
              borderRadius: 8,
              padding: '12px 14px',
              fontFamily: FONT_MONO,
              fontSize: 14,
              color: COLORS.text,
              outline: 'none',
              letterSpacing: 2,
              width: '100%',
              boxSizing: 'border-box',
            }}
          />
          {error && (
            <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 1, color: COLORS.brand, textTransform: 'uppercase' }}>
              {t({ es: '✕  Código incorrecto', en: '✕  Invalid code', pt: '✕  Código incorreto' }, lang)}
            </div>
          )}
          <div style={{ display: 'flex', gap: 8, marginTop: 4 }}>
            <button
              type="submit"
              disabled={loading || !code.trim()}
              style={{
                flex: 1,
                padding: '12px 20px',
                background: COLORS.brand,
                color: '#fff',
                border: 'none',
                borderRadius: 8,
                fontFamily: FONT_MONO,
                fontSize: 11,
                letterSpacing: 1.5,
                textTransform: 'uppercase',
                cursor: loading || !code.trim() ? 'not-allowed' : 'pointer',
                opacity: loading || !code.trim() ? 0.5 : 1,
                transition: 'opacity .15s',
              }}
            >
              {loading
                ? t({ es: 'Verificando…', en: 'Verifying…', pt: 'Verificando…' }, lang)
                : t({ es: 'Acceder', en: 'Access', pt: 'Acessar' }, lang)
              }
            </button>
            <button
              type="button"
              onClick={onClose}
              style={{
                padding: '12px 16px',
                background: 'none',
                color: COLORS.textMut,
                border: `1px solid ${COLORS.line}`,
                borderRadius: 8,
                fontFamily: FONT_MONO,
                fontSize: 11,
                letterSpacing: 1.5,
                textTransform: 'uppercase',
                cursor: 'pointer',
              }}
            >
              {t({ es: 'Cancelar', en: 'Cancel', pt: 'Cancelar' }, lang)}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

const ResourcesBlock = ({ productId }) => {
  const { lang } = useLang();
  const [pdfOpen, setPdfOpen] = useState(false);
  const [hwOpen, setHwOpen] = useState(false);
  const [instOpen, setInstOpen] = useState(false);
  const [lockModalOpen, setLockModalOpen] = useState(false);
  const [unlocked, setUnlocked] = useState(() => sessionStorage.getItem(DOCS_SESSION_KEY) === '1');
  const D = window[BRAND.dataKey];

  const ds = D.datasheets && D.datasheets[productId];
  const dsUrl = ds ? (ds[lang] || ds.es || ds.en || ds.pt) : null;
  const hwUrl = D.hardwareGuides && D.hardwareGuides[productId] ? D.hardwareGuides[productId].en : null;
  const instUrl = D.installGuides && D.installGuides[productId] ? D.installGuides[productId].en : null;

  if (!dsUrl && !hwUrl && !instUrl) return null;

  return (
    <div style={{ marginTop: 32, paddingTop: 24, borderTop: `1px solid ${COLORS.line}` }}>
      <div style={{ fontFamily: FONT_MONO, fontSize: 10, letterSpacing: 2, color: COLORS.brand, textTransform: 'uppercase', marginBottom: 14 }}>
        {t({ es: "Documentos", en: "Documents", pt: "Documentos" }, lang)}
      </div>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        {dsUrl && (
          <DocButton
            icon="&#128196;"
            label={t({ es: "Ficha técnica (PDF)", en: "Datasheet (PDF)", pt: "Ficha técnica (PDF)" }, lang)}
            fileName={dsUrl.split('/').pop()}
            onClick={() => setPdfOpen(true)}
          />
        )}
        {hwUrl && (unlocked ? (
          <DocButton
            icon="&#128295;"
            label={t({ es: "Hardware Guide (PDF)", en: "Hardware Guide (PDF)", pt: "Hardware Guide (PDF)" }, lang)}
            fileName={hwUrl.split('/').pop()}
            onClick={() => setHwOpen(true)}
          />
        ) : (
          <LockedDocButton
            label={t({ es: "Hardware Guide (PDF)", en: "Hardware Guide (PDF)", pt: "Hardware Guide (PDF)" }, lang)}
            onClick={() => setLockModalOpen(true)}
            lang={lang}
          />
        ))}
        {instUrl && (unlocked ? (
          <DocButton
            icon="&#128221;"
            label={t({ es: "Installation Guide (PDF)", en: "Installation Guide (PDF)", pt: "Installation Guide (PDF)" }, lang)}
            fileName={instUrl.split('/').pop()}
            onClick={() => setInstOpen(true)}
          />
        ) : (
          <LockedDocButton
            label={t({ es: "Installation Guide (PDF)", en: "Installation Guide (PDF)", pt: "Installation Guide (PDF)" }, lang)}
            onClick={() => setLockModalOpen(true)}
            lang={lang}
          />
        ))}
      </div>

      {pdfOpen && dsUrl && <PdfModal url={dsUrl} title={dsUrl.split('/').pop()} onClose={() => setPdfOpen(false)} />}
      {hwOpen && hwUrl && <PdfModal url={hwUrl} title={hwUrl.split('/').pop()} onClose={() => setHwOpen(false)} />}
      {instOpen && instUrl && <PdfModal url={instUrl} title={instUrl.split('/').pop()} onClose={() => setInstOpen(false)} />}
      {lockModalOpen && !unlocked && (
        <DocAccessModal
          onClose={() => setLockModalOpen(false)}
          onUnlock={() => { setUnlocked(true); setLockModalOpen(false); }}
          lang={lang}
        />
      )}
    </div>
  );
};

Object.assign(window, { ShowroomScene, ResourcesBlock, PdfModal });
