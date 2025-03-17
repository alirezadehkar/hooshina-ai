import { useEffect, useRef, useState } from '@wordpress/element';

const ContentReveal = ({ 
  content, 
  type = 'text', 
  effect = 'fade',
  duration = 800,
  delay = 0, 
  direction = 'up', 
  threshold = 0.1, 
  className = '', 
  onReveal = () => {}, 
}) => {
  const elementRef = useRef(null);
  const [isVisible, setIsVisible] = useState(false);
  const [isAnimationComplete, setIsAnimationComplete] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !isVisible) {
          setIsVisible(true);
        }
      },
      { threshold }
    );

    if (elementRef.current) {
      observer.observe(elementRef.current);
    }

    return () => {
      if (elementRef.current) {
        observer.unobserve(elementRef.current);
      }
    };
  }, [threshold, isVisible]);

  const getAnimationStyles = () => {
    if (!isVisible) {
      switch (effect) {
        case 'fade':
          return { opacity: 0 };
        case 'slide':
          switch (direction) {
            case 'up':
              return { opacity: 0, transform: 'translateY(30px)' };
            case 'down':
              return { opacity: 0, transform: 'translateY(-30px)' };
            case 'left':
              return { opacity: 0, transform: 'translateX(30px)' };
            case 'right':
              return { opacity: 0, transform: 'translateX(-30px)' };
            default:
              return { opacity: 0, transform: 'translateY(30px)' };
          }
        case 'zoom':
          return { opacity: 0, transform: 'scale(0.9)' };
        default:
          return { opacity: 0 };
      }
    }

    return { 
      opacity: 1, 
      transform: 'translateY(0) translateX(0) scale(1)',
      transition: `opacity ${duration}ms ease-out ${delay}ms, transform ${duration}ms ease-out ${delay}ms`
    };
  };

  const renderContent = () => {
    switch (type) {
      case 'image':
        return <img src={content} alt="" style={{ maxWidth: '100%', height: 'auto' }} />;
      case 'html':
        return <div dangerouslySetInnerHTML={{ __html: content }} />;
      case 'typewriter':
        return <TypewriterText text={content} isVisible={isVisible} delay={delay} duration={duration} onComplete={() => {
          setIsAnimationComplete(true);
          onReveal();
        }} />;
      default:
        return <div>{content}</div>;
    }
  };

  useEffect(() => {
    if (isVisible && effect !== 'typewriter') {
      const timer = setTimeout(() => {
        setIsAnimationComplete(true);
        onReveal();
      }, duration + delay);
      
      return () => clearTimeout(timer);
    }
  }, [isVisible, duration, delay, onReveal, effect]);

  return (
    <div 
      ref={elementRef}
      className={`content-reveal ${effect} ${isVisible ? 'visible' : ''} ${className}`}
      style={effect === 'typewriter' ? {} : getAnimationStyles()}
      data-animation-complete={isAnimationComplete}
    >
      {renderContent()}
    </div>
  );
};

const TypewriterText = ({ text, isVisible, delay, duration, onComplete }) => {
  const [displayText, setDisplayText] = useState('');
  
  useEffect(() => {
    if (!isVisible) return;
    
    const charactersPerMs = text.length / duration;
    let currentIndex = 0;
    let startTime = null;
    
    const animateText = (timestamp) => {
      if (!startTime) startTime = timestamp;
      const elapsedTime = timestamp - startTime - delay;
      
      if (elapsedTime <= 0) {
        requestAnimationFrame(animateText);
        return;
      }
      
      const charactersToShow = Math.floor(elapsedTime * charactersPerMs);
      
      if (charactersToShow > currentIndex) {
        currentIndex = Math.min(charactersToShow, text.length);
        setDisplayText(text.substring(0, currentIndex));
        
        if (currentIndex >= text.length) {
          onComplete();
          return;
        }
      }
      
      requestAnimationFrame(animateText);
    };
    
    const animationId = requestAnimationFrame(animateText);
    return () => cancelAnimationFrame(animationId);
  }, [text, isVisible, delay, duration, onComplete]);
  
  return <div>{displayText}</div>;
};

const applyRevealToHTML = (html, options = {}) => {
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = html;
  
  const textElements = tempDiv.querySelectorAll('p, h1, h2, h3, h4, h5, h6, img');
  
  let delay = options.initialDelay || 0;
  const delayIncrement = options.delayIncrement || 100;
  
  textElements.forEach((element, index) => {
    const currentDelay = delay + (index * delayIncrement);
    
    element.classList.add('content-reveal', options.effect || 'fade');
    element.dataset.delay = currentDelay;
    element.dataset.duration = options.duration || 800;
    element.dataset.effect = options.effect || 'fade';
    
    if (element.tagName.toLowerCase() === 'img') {
      element.style.opacity = '0';
    }
  });
  
  return tempDiv.innerHTML;
};

const enhanceApplyContentToEditor = (originalFunction) => {
  return function({content, type, block = null, options}) {
    if (options?.applyReveal) {
      if (type === 'html') {
        content = applyRevealToHTML(content, options.revealOptions || {});
      }
      
      const styleElement = document.createElement('style');
      styleElement.textContent = `
        .content-reveal {
          will-change: opacity, transform;
        }
        .content-reveal.fade {
          opacity: 0;
          transition: opacity var(--duration, 800ms) ease-out var(--delay, 0ms);
        }
        .content-reveal.slide {
          opacity: 0;
          transform: translateY(30px);
          transition: opacity var(--duration, 800ms) ease-out var(--delay, 0ms),
                      transform var(--duration, 800ms) ease-out var(--delay, 0ms);
        }
        .content-reveal.zoom {
          opacity: 0;
          transform: scale(0.9);
          transition: opacity var(--duration, 800ms) ease-out var(--delay, 0ms),
                      transform var(--duration, 800ms) ease-out var(--delay, 0ms);
        }
        .content-reveal.visible, .content-reveal.fade.visible, .content-reveal.slide.visible, .content-reveal.zoom.visible {
          opacity: 1;
          transform: translateY(0) scale(1);
        }
      `;
      
      if (!document.querySelector('#content-reveal-styles')) {
        styleElement.id = 'content-reveal-styles';
        document.head.appendChild(styleElement);
      }
      
      const script = document.createElement('script');
      script.textContent = `
        document.addEventListener('DOMContentLoaded', function() {
          const observer = new IntersectionObserver(
            (entries) => {
              entries.forEach(entry => {
                if (entry.isIntersecting) {
                  const element = entry.target;
                  element.style.setProperty('--delay', element.dataset.delay + 'ms');
                  element.style.setProperty('--duration', element.dataset.duration + 'ms');
                  
                  setTimeout(() => {
                    element.classList.add('visible');
                  }, 10);
                  
                  observer.unobserve(element);
                }
              });
            },
            { threshold: 0.1 }
          );
          
          const revealElements = document.querySelectorAll('.content-reveal');
          revealElements.forEach(element => {
            observer.observe(element);
          });
        });
      `;
      
      if (!document.querySelector('#content-reveal-script')) {
        script.id = 'content-reveal-script';
        document.body.appendChild(script);
      }
    }
    
    return originalFunction({content, type, block, options});
  };
};

export { ContentReveal, TypewriterText, applyRevealToHTML, enhanceApplyContentToEditor };