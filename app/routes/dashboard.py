from fastapi import APIRouter, Request, Depends
from fastapi.responses import HTMLResponse
from fastapi.templating import Jinja2Templates
from sqlalchemy.orm import Session
from sqlalchemy import func
from ..database import get_db
from ..models.lead import Lead, SimulacaoStatus
from ..models.simulacao import Simulacao
from ..dependencies import get_current_user

router = APIRouter()
templates = Jinja2Templates(directory="app/templates")

@router.get("/dashboard", response_class=HTMLResponse)
async def dashboard(request: Request, db: Session = Depends(get_db), current_user=Depends(get_current_user)):
    if not current_user:
        return RedirectResponse(url="/login", status_code=303)

    # Totais básicos
    total_leads = db.query(func.count(Lead.id)).scalar()
    total_simulados = db.query(func.count(Simulacao.id)).scalar()
    total_simulacao_realizada = db.query(func.count(Simulacao.id)).filter(
        Simulacao.status == SimulacaoStatus.SIMULACAO_REALIZADA
    ).scalar()
    soma_valores = db.query(func.sum(Simulacao.valor_simulado)).filter(
        Simulacao.status == SimulacaoStatus.SIMULACAO_REALIZADA
    ).scalar() or 0

    context = {
        "request": request,
        "user": current_user,
        "total_leads": total_leads,
        "total_simulados": total_simulados,
        "total_realizada": total_simulacao_realizada,
        "soma_valores": f"R$ {soma_valores:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")
    }

    return templates.TemplateResponse("dashboard.html", context)
