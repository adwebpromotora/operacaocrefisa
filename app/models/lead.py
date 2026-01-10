from sqlalchemy import Column, Integer, String, Enum, ForeignKey, DateTime, DECIMAL, Text
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
from ..database import Base
import enum

class SimulacaoStatus(str, enum.Enum):
    PENDENTE = "pendente"
    SEM_CADASTRO = "sem_cadastro"
    SEM_PERFIL = "sem_perfil"
    SIMULACAO_REALIZADA = "simulacao_realizada"

class Lead(Base):
    __tablename__ = "leads"

    id = Column(Integer, primary_key=True, index=True)
    cpf = Column(String(14), unique=True, nullable=False, index=True)
    nome = Column(String(255), nullable=False)
    telefone1 = Column(String(15))
    telefone2 = Column(String(15))
    email = Column(String(255))
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

    simulacao = relationship("Simulacao", back_populates="lead", uselist=False)
    anotacoes = relationship("Anotacao", back_populates="lead")
