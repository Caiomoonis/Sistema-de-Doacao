import { createFileRoute } from "@tanstack/react-router";
import { useEffect } from "react";

export const Route = createFileRoute("/")({
  component: Index,
  head: () => ({
    meta: [
      { title: "DoarMais — Sistema de Doações" },
      { name: "description", content: "Plataforma de doações em HTML, CSS e JavaScript puro." },
    ],
  }),
});

function Index() {
  useEffect(() => {
    window.location.replace("/doacoes/index.html");
  }, []);
  return (
    <div style={{ minHeight: "100vh", display: "grid", placeItems: "center", fontFamily: "system-ui" }}>
      <p>
        Abrindo a página…{" "}
        <a href="/doacoes/index.html" style={{ color: "#ff5a5f" }}>
          clique aqui se não for redirecionado
        </a>
        .
      </p>
    </div>
  );
}