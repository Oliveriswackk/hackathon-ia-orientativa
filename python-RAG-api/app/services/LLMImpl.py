from langchain_groq import ChatGroq
import os

from app.core.interfaces.ILLM import ILLM


class LLMImpl (ILLM):

    def __init__(self):
        self.llamaModel = ChatGroq(
            api_key=os.getenv('GROQ_API_KEY'),
            model="llama-3.3-70b-versatile",
            temperature=0.7,
            max_tokens=2048
        )

    def send_prompt(self, prompt: str) -> str:
        response = self.llamaModel.invoke([
            prompt
        ])
        return response.text()

