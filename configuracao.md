"use client"

import { useState } from "react"
import {
  Building2,
  User,
  Bell,
  Shield,
  Globe,
  Palette,
  Users,
  Link2,
  Phone,
  Mail,
  MapPin,
  Camera,
  Save,
  Clock,
  MessageSquare,
  Smartphone,
  CalendarDays,
  ChevronRight,
  Eye,
  EyeOff,
  Trash2,
  Plus,
  Instagram,
  ExternalLink,
  Check,
} from "lucide-react"
import { cn } from "@/lib/utils"

type TabId =
  | "negocio"
  | "equipe"
  | "notificacoes"
  | "integracao"
  | "aparencia"
  | "seguranca"

interface Tab {
  id: TabId
  label: string
  icon: typeof Building2
}

const tabs: Tab[] = [
  { id: "negocio", label: "Negocio", icon: Building2 },
  { id: "equipe", label: "Equipe", icon: Users },
  { id: "notificacoes", label: "Notificacoes", icon: Bell },
  { id: "integracao", label: "Integracoes", icon: Link2 },
  { id: "aparencia", label: "Aparencia", icon: Palette },
  { id: "seguranca", label: "Seguranca", icon: Shield },
]

interface TeamMember {
  id: string
  name: string
  email: string
  role: "admin" | "profissional" | "recepcionista"
  active: boolean
  avatar: string
}

const teamMembers: TeamMember[] = [
  { id: "1", name: "Joao Dias", email: "joao@zenith.com", role: "admin", active: true, avatar: "JD" },
  { id: "2", name: "Ana Costa", email: "ana@zenith.com", role: "profissional", active: true, avatar: "AC" },
  { id: "3", name: "Pedro Oliveira", email: "pedro@zenith.com", role: "profissional", active: true, avatar: "PO" },
  { id: "4", name: "Carla Santos", email: "carla@zenith.com", role: "profissional", active: true, avatar: "CS" },
  { id: "5", name: "Lucia Pereira", email: "lucia@zenith.com", role: "recepcionista", active: false, avatar: "LP" },
]

const roleLabels: Record<string, string> = {
  admin: "Administrador",
  profissional: "Profissional",
  recepcionista: "Recepcionista",
}

export default function ConfiguracoesPage() {
  const [activeTab, setActiveTab] = useState<TabId>("negocio")
  const [saved, setSaved] = useState(false)

  // Negocio
  const [businessName, setBusinessName] = useState("Zenith Studio")
  const [businessEmail, setBusinessEmail] = useState("contato@zenithstudio.com")
  const [businessPhone, setBusinessPhone] = useState("(11) 3456-7890")
  const [businessAddress, setBusinessAddress] = useState("Rua Augusta, 1234 - Sao Paulo, SP")
  const [businessCnpj, setBusinessCnpj] = useState("12.345.678/0001-90")
  const [timezone, setTimezone] = useState("America/Sao_Paulo")
  const [currency, setCurrency] = useState("BRL")

  // Notificacoes
  const [emailAgendamento, setEmailAgendamento] = useState(true)
  const [emailCancelamento, setEmailCancelamento] = useState(true)
  const [emailLembrete, setEmailLembrete] = useState(true)
  const [emailMarketing, setEmailMarketing] = useState(false)
  const [whatsAgendamento, setWhatsAgendamento] = useState(true)
  const [whatsCancelamento, setWhatsCancelamento] = useState(true)
  const [whatsLembrete, setWhatsLembrete] = useState(true)
  const [lembreteHoras, setLembreteHoras] = useState("2")

  // Aparencia
  const [bookingTheme, setBookingTheme] = useState<"light" | "dark">("light")
  const [showLogo, setShowLogo] = useState(true)
  const [showAddress, setShowAddress] = useState(true)
  const [showPhone, setShowPhone] = useState(true)

  // Seguranca
  const [showPassword, setShowPassword] = useState(false)

  const handleSave = () => {
    setSaved(true)
    setTimeout(() => setSaved(false), 2000)
  }

  return (
    <>
      <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-xl font-semibold tracking-tight text-foreground">
            Configuracoes
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Gerencie as configuracoes do seu negocio.
          </p>
        </div>
        <button
          onClick={handleSave}
          className={cn(
            "flex items-center gap-2 self-start rounded-md px-4 py-2 text-sm font-medium transition-all",
            saved
              ? "bg-emerald-600 text-white"
              : "bg-primary text-primary-foreground hover:bg-primary/90"
          )}
        >
          {saved ? (
            <>
              <Check className="h-4 w-4" />
              Salvo
            </>
          ) : (
            <>
              <Save className="h-4 w-4" />
              Salvar alteracoes
            </>
          )}
        </button>
      </div>

      {/* Tab navigation */}
      <div className="mb-6 overflow-x-auto">
        <div className="flex gap-1 border-b border-border">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={cn(
                "flex shrink-0 items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors",
                activeTab === tab.id
                  ? "border-foreground text-foreground"
                  : "border-transparent text-muted-foreground hover:text-foreground"
              )}
            >
              <tab.icon className="h-4 w-4" />
              <span className="hidden sm:inline">{tab.label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Tab content */}
      <div className="flex flex-col gap-6">
        {/* NEGOCIO */}
        {activeTab === "negocio" && (
          <>
            {/* Dados do negocio */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Dados do negocio
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Informacoes basicas que aparecem para seus clientes.
                </p>
              </div>
              <div className="p-5">
                {/* Logo upload */}
                <div className="mb-6 flex items-center gap-4">
                  <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-primary text-xl font-bold text-primary-foreground">
                    Z
                  </div>
                  <div>
                    <button className="flex items-center gap-2 rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-accent">
                      <Camera className="h-3.5 w-3.5" />
                      Alterar logo
                    </button>
                    <p className="mt-1 text-[10px] text-muted-foreground">
                      JPG, PNG ou SVG. Max 2MB.
                    </p>
                  </div>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Nome do negocio
                    </label>
                    <input
                      type="text"
                      value={businessName}
                      onChange={(e) => setBusinessName(e.target.value)}
                      className="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                    />
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      CNPJ
                    </label>
                    <input
                      type="text"
                      value={businessCnpj}
                      onChange={(e) => setBusinessCnpj(e.target.value)}
                      className="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                    />
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Email
                    </label>
                    <div className="relative">
                      <Mail className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                      <input
                        type="email"
                        value={businessEmail}
                        onChange={(e) => setBusinessEmail(e.target.value)}
                        className="h-9 w-full rounded-md border border-border bg-background pl-9 pr-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                      />
                    </div>
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Telefone
                    </label>
                    <div className="relative">
                      <Phone className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                      <input
                        type="tel"
                        value={businessPhone}
                        onChange={(e) => setBusinessPhone(e.target.value)}
                        className="h-9 w-full rounded-md border border-border bg-background pl-9 pr-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                      />
                    </div>
                  </div>
                  <div className="flex flex-col gap-1.5 sm:col-span-2">
                    <label className="text-xs font-medium text-foreground">
                      Endereco
                    </label>
                    <div className="relative">
                      <MapPin className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                      <input
                        type="text"
                        value={businessAddress}
                        onChange={(e) => setBusinessAddress(e.target.value)}
                        className="h-9 w-full rounded-md border border-border bg-background pl-9 pr-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Regional */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Regional
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Fuso horario e moeda.
                </p>
              </div>
              <div className="p-5">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Fuso horario
                    </label>
                    <div className="relative">
                      <Globe className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                      <select
                        value={timezone}
                        onChange={(e) => setTimezone(e.target.value)}
                        className="h-9 w-full appearance-none rounded-md border border-border bg-background pl-9 pr-8 text-sm text-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                      >
                        <option value="America/Sao_Paulo">Brasilia (GMT-3)</option>
                        <option value="America/Manaus">Manaus (GMT-4)</option>
                        <option value="America/Belem">Belem (GMT-3)</option>
                        <option value="America/Fortaleza">Fortaleza (GMT-3)</option>
                        <option value="America/Noronha">Fernando de Noronha (GMT-2)</option>
                      </select>
                    </div>
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Moeda
                    </label>
                    <select
                      value={currency}
                      onChange={(e) => setCurrency(e.target.value)}
                      className="h-9 w-full appearance-none rounded-md border border-border bg-background px-3 text-sm text-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                    >
                      <option value="BRL">Real Brasileiro (R$)</option>
                      <option value="USD">Dolar Americano ($)</option>
                      <option value="EUR">Euro</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            {/* Horario de funcionamento */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Horario de funcionamento
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Horarios gerais do estabelecimento.
                </p>
              </div>
              <div className="divide-y divide-border">
                {[
                  { day: "Segunda-feira", open: "08:00", close: "18:00", active: true },
                  { day: "Terca-feira", open: "08:00", close: "18:00", active: true },
                  { day: "Quarta-feira", open: "08:00", close: "18:00", active: true },
                  { day: "Quinta-feira", open: "08:00", close: "18:00", active: true },
                  { day: "Sexta-feira", open: "08:00", close: "18:00", active: true },
                  { day: "Sabado", open: "08:00", close: "14:00", active: true },
                  { day: "Domingo", open: "", close: "", active: false },
                ].map((item) => (
                  <div key={item.day} className="flex items-center justify-between px-5 py-3">
                    <div className="flex items-center gap-3">
                      <button
                        className={cn(
                          "flex h-5 w-9 items-center rounded-full transition-colors",
                          item.active ? "bg-foreground" : "bg-border"
                        )}
                      >
                        <span
                          className={cn(
                            "h-4 w-4 rounded-full bg-background transition-transform",
                            item.active ? "translate-x-[18px]" : "translate-x-0.5"
                          )}
                        />
                      </button>
                      <span
                        className={cn(
                          "text-sm",
                          item.active ? "font-medium text-foreground" : "text-muted-foreground"
                        )}
                      >
                        {item.day}
                      </span>
                    </div>
                    {item.active ? (
                      <div className="flex items-center gap-2">
                        <div className="flex items-center gap-1">
                          <Clock className="h-3 w-3 text-muted-foreground" />
                          <span className="text-xs tabular-nums text-foreground">
                            {item.open}
                          </span>
                        </div>
                        <span className="text-xs text-muted-foreground">-</span>
                        <span className="text-xs tabular-nums text-foreground">
                          {item.close}
                        </span>
                      </div>
                    ) : (
                      <span className="text-xs text-muted-foreground">Fechado</span>
                    )}
                  </div>
                ))}
              </div>
            </div>
          </>
        )}

        {/* EQUIPE */}
        {activeTab === "equipe" && (
          <>
            <div className="rounded-lg border border-border bg-card">
              <div className="flex items-center justify-between border-b border-border px-5 py-4">
                <div>
                  <h3 className="text-sm font-medium text-foreground">
                    Membros da equipe
                  </h3>
                  <p className="mt-0.5 text-xs text-muted-foreground">
                    {teamMembers.length} membros cadastrados
                  </p>
                </div>
                <button className="flex items-center gap-2 rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                  <Plus className="h-3.5 w-3.5" />
                  Convidar
                </button>
              </div>
              <div className="divide-y divide-border">
                {teamMembers.map((member) => (
                  <div
                    key={member.id}
                    className="flex items-center justify-between px-5 py-3.5 transition-colors hover:bg-accent/50"
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className={cn(
                          "flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-medium",
                          member.active
                            ? "bg-primary text-primary-foreground"
                            : "bg-muted text-muted-foreground"
                        )}
                      >
                        {member.avatar}
                      </div>
                      <div className="flex flex-col">
                        <div className="flex items-center gap-2">
                          <span className="text-sm font-medium text-foreground">
                            {member.name}
                          </span>
                          {!member.active && (
                            <span className="rounded-full bg-muted px-1.5 py-0.5 text-[9px] font-medium text-muted-foreground">
                              Inativo
                            </span>
                          )}
                        </div>
                        <span className="text-xs text-muted-foreground">
                          {member.email}
                        </span>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <span
                        className={cn(
                          "hidden rounded-full px-2 py-0.5 text-[10px] font-medium sm:inline",
                          member.role === "admin"
                            ? "bg-foreground/10 text-foreground"
                            : "bg-muted text-muted-foreground"
                        )}
                      >
                        {roleLabels[member.role]}
                      </span>
                      <ChevronRight className="h-4 w-4 text-muted-foreground" />
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Permissoes */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Funcoes e permissoes
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Defina o que cada funcao pode fazer no sistema.
                </p>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b border-border">
                      <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Permissao
                      </th>
                      <th className="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Admin
                      </th>
                      <th className="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Profissional
                      </th>
                      <th className="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Recepcionista
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border">
                    {[
                      { name: "Ver agenda", admin: true, pro: true, recep: true },
                      { name: "Criar agendamentos", admin: true, pro: true, recep: true },
                      { name: "Gerenciar servicos", admin: true, pro: false, recep: false },
                      { name: "Gerenciar clientes", admin: true, pro: true, recep: true },
                      { name: "Ver relatorios", admin: true, pro: false, recep: false },
                      { name: "Ver financeiro", admin: true, pro: false, recep: false },
                      { name: "Configuracoes", admin: true, pro: false, recep: false },
                    ].map((perm) => (
                      <tr key={perm.name}>
                        <td className="whitespace-nowrap px-5 py-3 text-xs text-foreground">
                          {perm.name}
                        </td>
                        <td className="px-5 py-3 text-center">
                          <div className="flex justify-center">
                            <div
                              className={cn(
                                "flex h-4.5 w-4.5 items-center justify-center rounded",
                                perm.admin ? "bg-foreground" : "border border-border"
                              )}
                            >
                              {perm.admin && (
                                <Check className="h-3 w-3 text-background" />
                              )}
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-3 text-center">
                          <div className="flex justify-center">
                            <div
                              className={cn(
                                "flex h-4.5 w-4.5 items-center justify-center rounded",
                                perm.pro ? "bg-foreground" : "border border-border"
                              )}
                            >
                              {perm.pro && (
                                <Check className="h-3 w-3 text-background" />
                              )}
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-3 text-center">
                          <div className="flex justify-center">
                            <div
                              className={cn(
                                "flex h-4.5 w-4.5 items-center justify-center rounded",
                                perm.recep ? "bg-foreground" : "border border-border"
                              )}
                            >
                              {perm.recep && (
                                <Check className="h-3 w-3 text-background" />
                              )}
                            </div>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </>
        )}

        {/* NOTIFICACOES */}
        {activeTab === "notificacoes" && (
          <>
            {/* Lembrete */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Lembrete de agendamento
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Envie lembretes automaticos antes do horario marcado.
                </p>
              </div>
              <div className="p-5">
                <div className="flex flex-col gap-1.5">
                  <label className="text-xs font-medium text-foreground">
                    Antecipar lembrete em
                  </label>
                  <div className="flex items-center gap-2">
                    <select
                      value={lembreteHoras}
                      onChange={(e) => setLembreteHoras(e.target.value)}
                      className="h-9 w-28 appearance-none rounded-md border border-border bg-background px-3 text-sm text-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                    >
                      <option value="1">1 hora</option>
                      <option value="2">2 horas</option>
                      <option value="3">3 horas</option>
                      <option value="6">6 horas</option>
                      <option value="12">12 horas</option>
                      <option value="24">24 horas</option>
                    </select>
                    <span className="text-xs text-muted-foreground">
                      antes do horario agendado
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* Email */}
            <div className="rounded-lg border border-border bg-card">
              <div className="flex items-center gap-3 border-b border-border px-5 py-4">
                <div className="flex h-8 w-8 items-center justify-center rounded-md bg-secondary">
                  <Mail className="h-4 w-4 text-foreground" />
                </div>
                <div>
                  <h3 className="text-sm font-medium text-foreground">
                    Notificacoes por email
                  </h3>
                  <p className="mt-0.5 text-xs text-muted-foreground">
                    Emails enviados para voce e para o cliente.
                  </p>
                </div>
              </div>
              <div className="divide-y divide-border">
                {[
                  { label: "Novo agendamento", desc: "Quando um cliente faz um agendamento.", state: emailAgendamento, set: setEmailAgendamento },
                  { label: "Cancelamento", desc: "Quando um agendamento e cancelado.", state: emailCancelamento, set: setEmailCancelamento },
                  { label: "Lembrete", desc: "Lembrete automatico antes do horario.", state: emailLembrete, set: setEmailLembrete },
                  { label: "Marketing", desc: "Promocoes e novidades para clientes.", state: emailMarketing, set: setEmailMarketing },
                ].map((item) => (
                  <div key={item.label} className="flex items-center justify-between px-5 py-3.5">
                    <div className="flex flex-col">
                      <span className="text-sm text-foreground">{item.label}</span>
                      <span className="text-xs text-muted-foreground">{item.desc}</span>
                    </div>
                    <button
                      onClick={() => item.set(!item.state)}
                      className={cn(
                        "flex h-5 w-9 items-center rounded-full transition-colors",
                        item.state ? "bg-foreground" : "bg-border"
                      )}
                    >
                      <span
                        className={cn(
                          "h-4 w-4 rounded-full bg-background transition-transform",
                          item.state ? "translate-x-[18px]" : "translate-x-0.5"
                        )}
                      />
                    </button>
                  </div>
                ))}
              </div>
            </div>

            {/* WhatsApp */}
            <div className="rounded-lg border border-border bg-card">
              <div className="flex items-center gap-3 border-b border-border px-5 py-4">
                <div className="flex h-8 w-8 items-center justify-center rounded-md bg-secondary">
                  <MessageSquare className="h-4 w-4 text-foreground" />
                </div>
                <div>
                  <h3 className="text-sm font-medium text-foreground">
                    Notificacoes por WhatsApp
                  </h3>
                  <p className="mt-0.5 text-xs text-muted-foreground">
                    Mensagens automaticas via WhatsApp.
                  </p>
                </div>
              </div>
              <div className="divide-y divide-border">
                {[
                  { label: "Novo agendamento", desc: "Confirmacao automatica para o cliente.", state: whatsAgendamento, set: setWhatsAgendamento },
                  { label: "Cancelamento", desc: "Aviso de cancelamento ao profissional.", state: whatsCancelamento, set: setWhatsCancelamento },
                  { label: "Lembrete", desc: "Lembrete ao cliente antes do horario.", state: whatsLembrete, set: setWhatsLembrete },
                ].map((item) => (
                  <div key={item.label} className="flex items-center justify-between px-5 py-3.5">
                    <div className="flex flex-col">
                      <span className="text-sm text-foreground">{item.label}</span>
                      <span className="text-xs text-muted-foreground">{item.desc}</span>
                    </div>
                    <button
                      onClick={() => item.set(!item.state)}
                      className={cn(
                        "flex h-5 w-9 items-center rounded-full transition-colors",
                        item.state ? "bg-foreground" : "bg-border"
                      )}
                    >
                      <span
                        className={cn(
                          "h-4 w-4 rounded-full bg-background transition-transform",
                          item.state ? "translate-x-[18px]" : "translate-x-0.5"
                        )}
                      />
                    </button>
                  </div>
                ))}
              </div>
            </div>
          </>
        )}

        {/* INTEGRACOES */}
        {activeTab === "integracao" && (
          <>
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Integracoes disponiveis
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Conecte ferramentas externas ao seu sistema.
                </p>
              </div>
              <div className="divide-y divide-border">
                {[
                  {
                    name: "WhatsApp Business",
                    desc: "Envie mensagens automaticas e lembretes.",
                    icon: Smartphone,
                    connected: true,
                  },
                  {
                    name: "Google Calendar",
                    desc: "Sincronize agendamentos com sua agenda Google.",
                    icon: CalendarDays,
                    connected: true,
                  },
                  {
                    name: "Instagram",
                    desc: "Permita agendamentos direto pelo Instagram.",
                    icon: Instagram,
                    connected: false,
                  },
                  {
                    name: "Mercado Pago",
                    desc: "Receba pagamentos online dos seus clientes.",
                    icon: Link2,
                    connected: false,
                  },
                  {
                    name: "Stripe",
                    desc: "Processe pagamentos com cartao de credito.",
                    icon: Link2,
                    connected: false,
                  },
                ].map((integration) => (
                  <div
                    key={integration.name}
                    className="flex items-center justify-between px-5 py-4"
                  >
                    <div className="flex items-center gap-3">
                      <div className="flex h-10 w-10 items-center justify-center rounded-md bg-secondary">
                        <integration.icon className="h-4 w-4 text-foreground" />
                      </div>
                      <div className="flex flex-col">
                        <div className="flex items-center gap-2">
                          <span className="text-sm font-medium text-foreground">
                            {integration.name}
                          </span>
                          {integration.connected && (
                            <span className="rounded-full bg-emerald-500/10 px-1.5 py-0.5 text-[9px] font-medium text-emerald-600">
                              Conectado
                            </span>
                          )}
                        </div>
                        <span className="text-xs text-muted-foreground">
                          {integration.desc}
                        </span>
                      </div>
                    </div>
                    <button
                      className={cn(
                        "flex shrink-0 items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors",
                        integration.connected
                          ? "border border-border text-foreground hover:bg-accent"
                          : "bg-primary text-primary-foreground hover:bg-primary/90"
                      )}
                    >
                      {integration.connected ? (
                        "Configurar"
                      ) : (
                        <>
                          Conectar
                          <ExternalLink className="h-3 w-3" />
                        </>
                      )}
                    </button>
                  </div>
                ))}
              </div>
            </div>

            {/* API */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Chave de API
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Use sua chave de API para integrar com outros sistemas.
                </p>
              </div>
              <div className="p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                  <div className="flex flex-1 flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Chave de API
                    </label>
                    <input
                      type="text"
                      value="znt_sk_live_a1b2c3d4e5f6g7h8i9j0..."
                      readOnly
                      className="h-9 rounded-md border border-border bg-muted px-3 font-mono text-xs text-muted-foreground"
                    />
                  </div>
                  <button className="flex shrink-0 items-center gap-2 rounded-md border border-border px-3 py-2 text-xs font-medium text-foreground transition-colors hover:bg-accent">
                    Copiar chave
                  </button>
                </div>
                <p className="mt-2 text-[10px] text-muted-foreground">
                  Nunca compartilhe sua chave de API. Use apenas em servidores seguros.
                </p>
              </div>
            </div>
          </>
        )}

        {/* APARENCIA */}
        {activeTab === "aparencia" && (
          <>
            {/* Page de agendamento */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Pagina de agendamento
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Personalize como seus clientes veem a pagina de agendamento.
                </p>
              </div>
              <div className="p-5">
                {/* Theme */}
                <div className="mb-6">
                  <label className="mb-2 block text-xs font-medium text-foreground">
                    Tema da pagina
                  </label>
                  <div className="flex gap-3">
                    <button
                      onClick={() => setBookingTheme("light")}
                      className={cn(
                        "flex flex-col items-center gap-2 rounded-lg border-2 p-3 transition-colors",
                        bookingTheme === "light"
                          ? "border-foreground"
                          : "border-border hover:border-muted-foreground"
                      )}
                    >
                      <div className="h-16 w-24 rounded bg-background border border-border" />
                      <span className="text-xs text-foreground">Claro</span>
                    </button>
                    <button
                      onClick={() => setBookingTheme("dark")}
                      className={cn(
                        "flex flex-col items-center gap-2 rounded-lg border-2 p-3 transition-colors",
                        bookingTheme === "dark"
                          ? "border-foreground"
                          : "border-border hover:border-muted-foreground"
                      )}
                    >
                      <div className="h-16 w-24 rounded bg-foreground" />
                      <span className="text-xs text-foreground">Escuro</span>
                    </button>
                  </div>
                </div>

                {/* Visibility toggles */}
                <div className="flex flex-col gap-4">
                  <label className="mb-0 block text-xs font-medium text-foreground">
                    Exibir na pagina
                  </label>
                  {[
                    { label: "Logo do negocio", state: showLogo, set: setShowLogo },
                    { label: "Endereco", state: showAddress, set: setShowAddress },
                    { label: "Telefone", state: showPhone, set: setShowPhone },
                  ].map((item) => (
                    <div key={item.label} className="flex items-center justify-between">
                      <span className="text-sm text-foreground">{item.label}</span>
                      <button
                        onClick={() => item.set(!item.state)}
                        className={cn(
                          "flex h-5 w-9 items-center rounded-full transition-colors",
                          item.state ? "bg-foreground" : "bg-border"
                        )}
                      >
                        <span
                          className={cn(
                            "h-4 w-4 rounded-full bg-background transition-transform",
                            item.state ? "translate-x-[18px]" : "translate-x-0.5"
                          )}
                        />
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Link publico */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Link publico de agendamento
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Compartilhe este link para seus clientes agendarem online.
                </p>
              </div>
              <div className="p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                  <div className="flex flex-1 flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      URL
                    </label>
                    <input
                      type="text"
                      value="https://zenith.app/agendar/zenith-studio"
                      readOnly
                      className="h-9 rounded-md border border-border bg-muted px-3 text-xs text-muted-foreground"
                    />
                  </div>
                  <div className="flex gap-2">
                    <button className="flex shrink-0 items-center gap-2 rounded-md border border-border px-3 py-2 text-xs font-medium text-foreground transition-colors hover:bg-accent">
                      Copiar link
                    </button>
                    <button className="flex shrink-0 items-center gap-2 rounded-md border border-border px-3 py-2 text-xs font-medium text-foreground transition-colors hover:bg-accent">
                      <ExternalLink className="h-3 w-3" />
                      Abrir
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </>
        )}

        {/* SEGURANCA */}
        {activeTab === "seguranca" && (
          <>
            {/* Alterar senha */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Alterar senha
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Atualize sua senha regularmente para manter a seguranca.
                </p>
              </div>
              <div className="p-5">
                <div className="flex max-w-md flex-col gap-4">
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Senha atual
                    </label>
                    <div className="relative">
                      <input
                        type={showPassword ? "text" : "password"}
                        placeholder="Digite sua senha atual"
                        className="h-9 w-full rounded-md border border-border bg-background px-3 pr-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                      />
                      <button
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                        aria-label={showPassword ? "Ocultar senha" : "Mostrar senha"}
                      >
                        {showPassword ? (
                          <EyeOff className="h-3.5 w-3.5" />
                        ) : (
                          <Eye className="h-3.5 w-3.5" />
                        )}
                      </button>
                    </div>
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Nova senha
                    </label>
                    <input
                      type="password"
                      placeholder="Digite a nova senha"
                      className="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                    />
                  </div>
                  <div className="flex flex-col gap-1.5">
                    <label className="text-xs font-medium text-foreground">
                      Confirmar nova senha
                    </label>
                    <input
                      type="password"
                      placeholder="Confirme a nova senha"
                      className="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
                    />
                  </div>
                  <button className="flex w-fit items-center gap-2 rounded-md bg-primary px-4 py-2 text-xs font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                    Atualizar senha
                  </button>
                </div>
              </div>
            </div>

            {/* Sessoes */}
            <div className="rounded-lg border border-border bg-card">
              <div className="border-b border-border px-5 py-4">
                <h3 className="text-sm font-medium text-foreground">
                  Sessoes ativas
                </h3>
                <p className="mt-0.5 text-xs text-muted-foreground">
                  Dispositivos com acesso a sua conta.
                </p>
              </div>
              <div className="divide-y divide-border">
                {[
                  { device: "Chrome - Windows", location: "Sao Paulo, SP", current: true, lastActive: "Agora" },
                  { device: "Safari - iPhone", location: "Sao Paulo, SP", current: false, lastActive: "2h atras" },
                  { device: "Firefox - MacOS", location: "Rio de Janeiro, RJ", current: false, lastActive: "3 dias atras" },
                ].map((session) => (
                  <div key={session.device} className="flex items-center justify-between px-5 py-3.5">
                    <div className="flex items-center gap-3">
                      <div className="flex h-9 w-9 items-center justify-center rounded-md bg-secondary">
                        <Globe className="h-4 w-4 text-foreground" />
                      </div>
                      <div className="flex flex-col">
                        <div className="flex items-center gap-2">
                          <span className="text-sm text-foreground">{session.device}</span>
                          {session.current && (
                            <span className="rounded-full bg-emerald-500/10 px-1.5 py-0.5 text-[9px] font-medium text-emerald-600">
                              Atual
                            </span>
                          )}
                        </div>
                        <span className="text-xs text-muted-foreground">
                          {session.location} - {session.lastActive}
                        </span>
                      </div>
                    </div>
                    {!session.current && (
                      <button className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-destructive-foreground">
                        <Trash2 className="h-3.5 w-3.5" />
                      </button>
                    )}
                  </div>
                ))}
              </div>
            </div>

            {/* Zona de perigo */}
            <div className="rounded-lg border border-destructive/30 bg-card">
              <div className="border-b border-destructive/30 px-5 py-4">
                <h3 className="text-sm font-medium text-destructive-foreground">
                  Zona de perigo
                </h3>
              </div>
              <div className="p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <p className="text-sm text-foreground">Excluir conta</p>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                      Esta acao e irreversivel. Todos os dados serao permanentemente excluidos.
                    </p>
                  </div>
                  <button className="flex shrink-0 items-center gap-2 self-start rounded-md border border-destructive/30 px-4 py-2 text-xs font-medium text-destructive-foreground transition-colors hover:bg-destructive/10">
                    <Trash2 className="h-3.5 w-3.5" />
                    Excluir conta
                  </button>
                </div>
              </div>
            </div>
          </>
        )}
      </div>
    </>
  )
}
